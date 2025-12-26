<?php

namespace App\Filament\Resources\Lats\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Barryvdh\DomPDF\Facade\Pdf;

class SummaryRelationManager extends RelationManager
{
    protected static ?string $relationshipTitle = 'Financial Summary Report';

    protected static string $relationship = 'summaries';

    public function table(Table $table): Table
    {
        $lat = $this->getOwnerRecord();
        $materials = $lat->materials;
        $expenses = $lat->expenses;

        $initialInvestment = $lat->initial_investment ?? 0;
        $materialsTotal = $materials->sum('price');
        $expensesTotal = $expenses->sum('price');
        $totalCost = $materialsTotal + $expensesTotal;

        $profitPercentage = $lat->profit_percentage ?? 10;
        $profitAmount = ($totalCost * $profitPercentage) / 100;
        $sellingPrice = $totalCost + $profitAmount;

        $pieces = $lat->pieces ?? 1;
        $costPerPiece = $pieces > 0 ? $totalCost / $pieces : 0;
        $sellingPricePerPiece = $pieces > 0 ? $sellingPrice / $pieces : 0;
        $profitPerPiece = $pieces > 0 ? $profitAmount / $pieces : 0;

        // Organized summary data with sections
        $summaryData = [
            // COST BREAKDOWN SECTION
            ['type' => 'COST BREAKDOWN', 'amount' => null, 'is_header' => true, 'icon' => null],
            ['type' => 'Materials Cost', 'amount' => $materialsTotal, 'is_header' => false, 'description' => 'Raw materials purchased', 'icon' => 'heroicon-o-cube'],
            ['type' => 'Labor & Expenses', 'amount' => $expensesTotal, 'is_header' => false, 'description' => 'Workers and other costs', 'icon' => 'heroicon-o-users'],
            ['type' => 'Total Cost', 'amount' => $totalCost, 'is_header' => false, 'is_bold' => true, 'description' => 'All costs combined', 'icon' => 'heroicon-o-calculator'],

            // PRICING SECTION
            ['type' => 'PRICING', 'amount' => null, 'is_header' => true, 'icon' => null],
            ['type' => 'Profit Margin', 'amount' => $profitPercentage, 'is_percentage' => true, 'is_header' => false, 'description' => 'Target profit percentage', 'icon' => 'heroicon-o-chart-bar'],
            ['type' => 'Profit Amount', 'amount' => $profitAmount, 'is_header' => false, 'description' => 'Total profit earned', 'icon' => 'heroicon-o-currency-dollar'],
            ['type' => 'Final Selling Price', 'amount' => $sellingPrice, 'is_header' => false, 'is_bold' => true, 'description' => 'Total revenue from sales', 'icon' => 'heroicon-o-tag'],

            // PER UNIT BREAKDOWN
            ['type' => 'PER PIECE BREAKDOWN', 'amount' => null, 'is_header' => true, 'icon' => null],
            ['type' => 'Total Pieces', 'amount' => $pieces, 'is_quantity' => true, 'is_header' => false, 'description' => 'Number of units produced', 'icon' => 'heroicon-o-squares-2x2'],
            ['type' => 'Cost Per Piece', 'amount' => $costPerPiece, 'is_header' => false, 'description' => 'Manufacturing cost per unit', 'icon' => 'heroicon-o-shopping-cart'],
            ['type' => 'Profit Per Piece', 'amount' => $profitPerPiece, 'is_header' => false, 'description' => 'Profit earned per unit', 'icon' => 'heroicon-o-sparkles'],
            ['type' => 'Selling Price Per Piece', 'amount' => $sellingPricePerPiece, 'is_header' => false, 'is_bold' => true, 'description' => 'Price to charge customers', 'icon' => 'heroicon-o-receipt-percent'],
        ];

        $lat->summaries()->delete();
        foreach ($summaryData as $data) {
            $lat->summaries()->create($data);
        }

        return $table
            ->heading('Financial Summary & Pricing ')
            ->description('Easy-to-understand breakdown of costs, profits, and pricing')
            ->columns([
                TextColumn::make('type')
                    ->label('Item')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_header ?? false) {
                            return new HtmlString("
                                <div class='text-lg font-bold text-primary-600 border-b-2 border-primary-400 pb-2 mb-2'>
                                    {$state}
                                </div>
                            ");
                        }

                        $isBold = $record->is_bold ?? false;
                        $description = $record->description ?? '';
                        $fontWeight = $isBold ? 'font-bold' : 'font-medium';

                        return new HtmlString("
                            <div class='flex flex-col'>
                                <span class='{$fontWeight} text-gray-900'>{$state}</span>
                                " . ($description ? "<span class='text-xs text-gray-500 mt-1'>{$description}</span>" : "") . "
                            </div>
                        ");
                    })
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('Value')
                    ->alignEnd()
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->is_header ?? false) {
                            return '';
                        }

                        // Check for percentage more robustly
                        $isPercentage = ($record->is_percentage ?? false) || 
                                       (strpos($record->type ?? '', 'Profit Margin') !== false);
                        
                        if ($isPercentage) {
                            return new HtmlString("
                                <span class='inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800'>
                                    {$state}%
                                </span>
                            ");
                        }

                        if (($record->is_quantity ?? false) || strpos($record->type ?? '', 'Total Pieces') !== false) {
                            return new HtmlString("
                                <span class='inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800'>
                                    {$state} pieces
                                </span>
                            ");
                        }

                        $isBold = $record->is_bold ?? false;
                        $fontWeight = $isBold ? 'font-bold text-lg' : 'font-medium';
                        $textColor = $isBold ? 'text-success-600' : 'text-gray-900';
                        $formattedAmount = number_format($state, 2);

                        return new HtmlString("
                            <span class='{$fontWeight} {$textColor}'>PKR {$formattedAmount}</span>
                        ");
                    }),
            ])
            ->paginated(false)
            ->headerActions([
                Action::make('update_settings')
                    ->label('Update Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('primary')
                    ->form([
                        TextInput::make('pieces')
                            ->label('Total Pieces to Produce')
                            ->helperText('How many units will you make?')
                            ->numeric()
                            ->suffix('pieces')
                            ->default($lat->pieces ?? 1)
                            ->minValue(1)
                            ->required(),

                        Select::make('profit_percentage')
                            ->label('Profit Margin (%)')
                            ->helperText('How much profit do you want to make?')
                            ->options([
                                '5' => '5% - Low margin (competitive pricing)',
                                '10' => '10% - Standard margin',
                                '15' => '15% - Good margin',
                                '20' => '20% - High margin (recommended)',
                                '25' => '25% - Premium margin',
                                '30' => '30% - Luxury margin',
                                '40' => '40% - Very high margin',
                                '50' => '50% - Maximum margin',
                            ])
                            ->default($lat->profit_percentage ?? 20)
                            ->required(),
                    ])
                    ->action(function (array $data) use ($lat) {
                        $lat->update([
                            'pieces' => $data['pieces'],
                            'profit_percentage' => $data['profit_percentage'],
                        ]);

                        Notification::make()
                            ->title('Settings Updated!')
                            ->success()
                            ->body('Your financial calculations have been updated.')
                            ->send();
                    }),

                Action::make('download_report')
                    ->label('Download PDF Report')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () use ($lat) {
                        // Generate PDF report directly from HTML
                        $htmlContent = $this->generatePdfReport($lat);
                        $pdf = Pdf::loadHTML($htmlContent);
                        
                        // Return as PDF file
                        return response()->streamDownload(function () use ($pdf) {
                            echo $pdf->output();
                        }, 'lat-summary-' . $lat->lat_no . '-' . date('Y-m-d') . '.pdf', [
                            'Content-Type' => 'application/pdf',
                            'Content-Disposition' => 'attachment; filename="lat-summary-' . $lat->lat_no . '-' . date('Y-m-d') . '.pdf"',
                        ]);
                    }),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);

        return $table;
    }

    /**
     * Generate PDF report for the lat summary
     */
    private function generatePdfReport($lat): string
    {
        return $this->generateReadablePdfContent($lat);
    }

    /**
     * Generate compact 1-page PDF content
     */
    private function generateReadablePdfContent($lat): string
    {
        // Calculate values
        $materials = $lat->materials;
        $expenses = $lat->expenses;
        $materialsTotal = $materials->sum('price');
        $expensesTotal = $expenses->sum('price');
        $totalCost = $materialsTotal + $expensesTotal;
        $profitPercentage = $lat->profit_percentage ?? 10;
        $profitAmount = ($totalCost * $profitPercentage) / 100;
        $sellingPrice = $totalCost + $profitAmount;
        $pieces = $lat->pieces ?? 1;

        // Generate HTML content with compact layout
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lat Summary Report - ' . $lat->lat_no . '</title>
    <style>
        @page { 
            size: A4; 
            margin: 12mm; 
            @bottom-center { 
                content: "Page " counter(page) " of 1"; 
                font-size: 9px; 
                color: #666; 
            } 
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 0; 
            color: #000; 
            line-height: 1.2; 
            font-size: 10px;
        }
        .header { 
            text-align: center; 
            border-bottom: 2px solid #333; 
            padding-bottom: 8px; 
            margin-bottom: 12px; 
        }
        .company-name { font-size: 20px; font-weight: bold; color: #000; margin: 0; }
        .report-title { font-size: 14px; margin: 3px 0; color: #333; }
        .summary-cards { 
            display: flex; 
            flex-direction: row; 
            gap: 8px; 
            margin-bottom: 15px; 
        }
        .summary-card { 
            background: #f5f5f5; 
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            align-items: center;
            padding: 8px; 
            border: 1px solid #ccc; 
            border-radius: 3px; 
            text-align: center;
        }
        .summary-card h4 { 
            margin: 0 0 3px 0; 
            color: #000; 
            font-size: 10px; 
            font-weight: bold;
        }
        .summary-card .value { 
            font-size: 14px; 
            font-weight: bold; 
            color: #000; 
            margin: 0;
        }
        .summary-card .item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }
        .two-column {
            display: flex;
            gap: 12px;
            margin-bottom: 15px;
        }
        .two-column > div {
            flex: 1;
        }
        .section-title { 
            font-size: 12px; 
            font-weight: bold; 
            color: #000; 
            border-bottom: 1px solid #333; 
            padding-bottom: 3px; 
            margin-bottom: 8px; 
            text-transform: uppercase; 
            background: #f0f0f0;
            padding: 5px;
            border-radius: 2px;
        }
        .compact-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 12px; 
            border: 1px solid #ddd; 
            font-size: 9px;
        }
        .compact-table th, .compact-table td { 
            padding: 4px 6px; 
            text-align: left; 
            border-bottom: 1px solid #ddd; 
            vertical-align: top;
        }
        .compact-table th { 
            background: #e8e8e8; 
            font-weight: bold; 
            color: #000; 
            font-size: 9px;
        }
        .compact-table th:nth-child(2), .compact-table td:nth-child(2) { 
            text-align: right; 
            font-weight: bold; 
            min-width: 70px;
        }
        .compact-table th:nth-child(3), .compact-table td:nth-child(3) { 
            font-size: 8px; 
            color: #666;
            font-style: italic;
        }
        .total { 
            background: #e8e8e8; 
            font-weight: bold; 
            border-top: 2px solid #333; 
        }
        .total td { 
            color: #000; 
            font-weight: bold; 
        }
        .info-box {
            background: #f8f8f8;
            padding: 8px;
            border-radius: 3px;
            margin-bottom: 12px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Lotrix</div>
        <div class="report-title">Lat Financial Summary Report</div>
        <div>Lat No: ' . $lat->lat_no . ' | Date: ' . date('Y-m-d H:i:s') . '</div>
    </div>
    
    <div class="info-box">
        <strong>Customer:</strong> ' . $lat->customer_name . ' | 
        <strong>Design:</strong> ' . $lat->design_name . ' | 
        <strong>Pieces:</strong> ' . $pieces . '
    </div>

    <div class="summary-table">
        <table class="compact-table">
            <thead>
                <tr>
                    <th>Total Cost</th>
                    <th>Selling Price</th>
                    <th>Profit Amount</th>
                    <th>Profit Margin</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PKR ' . number_format($totalCost, 0) . '</td>
                    <td>PKR ' . number_format($sellingPrice, 0) . '</td>
                    <td>PKR ' . number_format($profitAmount, 0) . '</td>
                    <td>' . $profitPercentage . '%</td>
                </tr>
            </tbody>
        </table>
    </div>
    

   
    
    <div class="two-column">
        <div>
            <div class="section-title">Cost Breakdown</div>
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Amount (PKR)</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Materials Cost</td>
                        <td>' . number_format($materialsTotal, 0) . '</td>
                        <td>Raw materials</td>
                    </tr>
                    <tr>
                        <td>Labor & Expenses</td>
                        <td>' . number_format($expensesTotal, 0) . '</td>
                        <td>Workers & costs</td>
                    </tr>
                    <tr class="total">
                        <td><strong>Total Cost</strong></td>
                        <td><strong>' . number_format($totalCost, 0) . '</strong></td>
                        <td><strong>All costs</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div>
            <div class="section-title">Pricing Details</div>
            <table class="compact-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Value</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Profit Margin</td>
                        <td>' . $profitPercentage . '%</td>
                        <td>Target percentage</td>
                    </tr>
                    <tr>
                        <td>Profit Amount</td>
                        <td>PKR ' . number_format($profitAmount, 0) . '</td>
                        <td>Total profit</td>
                    </tr>
                    <tr class="total">
                        <td><strong>Final Selling Price</strong></td>
                        <td><strong>PKR ' . number_format($sellingPrice, 0) . '</strong></td>
                        <td><strong>Total revenue</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="section-title">Financial Summary</div>
    <table class="compact-table">
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>% of Cost</th>
                <th>Per Piece</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Materials Cost</td>
                <td>PKR ' . number_format($materialsTotal, 0) . '</td>
                <td>' . ($totalCost > 0 ? round(($materialsTotal/$totalCost)*100, 1) : 0) . '%</td>
                <td>PKR ' . number_format($materialsTotal/$pieces, 0) . '</td>
            </tr>
            <tr>
                <td>Labor & Expenses</td>
                <td>PKR ' . number_format($expensesTotal, 0) . '</td>
                <td>' . ($totalCost > 0 ? round(($expensesTotal/$totalCost)*100, 1) : 0) . '%</td>
                <td>PKR ' . number_format($expensesTotal/$pieces, 0) . '</td>
            </tr>
            <tr class="total">
                <td><strong>Total Cost</strong></td>
                <td><strong>PKR ' . number_format($totalCost, 0) . '</strong></td>
                <td><strong>100%</strong></td>
                <td><strong>PKR ' . number_format($totalCost/$pieces, 0) . '</strong></td>
            </tr>
            <tr>
                <td>Profit Amount</td>
                <td>PKR ' . number_format($profitAmount, 0) . '</td>
                <td>' . round($profitPercentage, 1) . '%</td>
                <td>PKR ' . number_format($profitAmount/$pieces, 0) . '</td>
            </tr>
            <tr class="total">
                <td><strong>Final Selling Price</strong></td>
                <td><strong>PKR ' . number_format($sellingPrice, 0) . '</strong></td>
                <td><strong>' . round(100 + $profitPercentage, 1) . '%</strong></td>
                <td><strong>PKR ' . number_format($sellingPrice / $pieces, 0) . '</strong></td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p><strong>Lotrix - Lat Management System</strong></p>
        <p>Report ID: ' . uniqid() . ' | Generated: ' . date('Y-m-d H:i:s') . '</p>
    </div>
</body>
</html>';
        
        return $html;
    }
}