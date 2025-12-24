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
            ['type' => 'Initial Investment', 'amount' => $initialInvestment, 'is_header' => false, 'description' => 'Starting capital', 'icon' => 'heroicon-o-banknotes'],
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

                        if ($record->is_quantity ?? false) {
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
                        TextInput::make('initial_investment')
                            ->label('Initial Investment')
                            ->helperText('Starting capital for this project')
                            ->numeric()
                            ->prefix('PKR')
                            ->default($lat->initial_investment ?? 0)
                            ->required(),

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
                            'initial_investment' => $data['initial_investment'],
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
                    ->label('Download Report')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function () use ($lat) {
                        // Generate HTML report content
                        $htmlContent = $this->generatePdfReport($lat);
                        
                        // Return as HTML file that can be printed/saved as PDF
                        return response()->streamDownload(function () use ($htmlContent) {
                            echo $htmlContent;
                        }, 'lat-summary-' . $lat->lat_no . '-' . date('Y-m-d') . '.html', [
                            'Content-Type' => 'text/html',
                            'Content-Disposition' => 'attachment; filename="lat-summary-' . $lat->lat_no . '-' . date('Y-m-d') . '.html"',
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
        // Calculate values
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
        
        // Summary data
        $summaryData = [
            ['Initial Investment', $initialInvestment, 'Starting capital'],
            ['Materials Cost', $materialsTotal, 'Raw materials purchased'],
            ['Labor & Expenses', $expensesTotal, 'Workers and other costs'],
            ['Total Cost', $totalCost, 'All costs combined'],
            ['Profit Margin', $profitPercentage . '%', 'Target profit percentage'],
            ['Profit Amount', $profitAmount, 'Total profit earned'],
            ['Final Selling Price', $sellingPrice, 'Total revenue from sales'],
            ['Total Pieces', $pieces . ' pieces', 'Number of units produced'],
            ['Cost Per Piece', $costPerPiece, 'Manufacturing cost per unit'],
            ['Profit Per Piece', $profitPerPiece, 'Profit earned per unit'],
            ['Selling Price Per Piece', $sellingPricePerPiece, 'Price to charge customers'],
        ];
        
        // Generate HTML content
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lat Summary Report - ' . $lat->lat_no . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #ddd; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #6366f1; }
        .report-title { font-size: 18px; margin: 10px 0; }
        .lat-info { background: #f8f9fa; padding: 30px; border-radius: 5px; margin-bottom: 20px; }
        .section { margin-bottom: 30px; }
        .section-title { font-size: 16px; font-weight: bold; color: #6366f1; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: bold; }
        th:nth-child(2) { text-align: right; }
        .amount { text-align: right; font-weight: bold; }
        .description { font-size: 12px; color: #666; }
        .total { background: #e7f5ff; font-weight: bold; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Lotrix</div>
        <div class="report-title">Lat Financial Summary Report</div>
        <div>Generated on: ' . date('Y-m-d H:i:s') . '</div>
    </div>
    
    <div class="lat-info">
        <strong>Lat No:</strong> ' . $lat->lat_no . '<br><br>
        <strong>Design:</strong> ' . $lat->design_name . '<br><br>
        <strong>Customer:</strong> ' . $lat->customer_name . '
    </div>
    
    <div class="section">
        <div class="section-title">Financial Summary</div>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Amount</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>';
        
        foreach ($summaryData as $item) {
            $amount = is_numeric($item[1]) ? 'PKR ' . number_format($item[1], 2) : $item[1];
            $isTotal = strpos($item[0], 'Total') !== false || strpos($item[0], 'Final') !== false;
            $rowClass = $isTotal ? 'total' : '';
            
            $html .= '<tr class="' . $rowClass . '">
                <td>' . $item[0] . '</td>
                <td class="amount">' . $amount . '</td>
                <td class="description">' . $item[2] . '</td>
            </tr>';
        }
        
        $html .= '</tbody>
        </table>
    </div>';
        
      
        
       
            
         
        
        $html .= '<div class="footer">
        <p>This report was generated by Lotrix - Lat Management System</p>
        <p>Report ID: ' . uniqid() . '</p>
    </div>
</body>
</html>';
        
        return $html;
    }
}