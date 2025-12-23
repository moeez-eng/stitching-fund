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

                        if ($record->is_percentage ?? false) {
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

                            // Action::make('download_report')
                            //     ->label('Download Report')
                            //     ->icon('heroicon-o-arrow-down-tray')
                            //     ->color('success')
                            //     ->action(function () use ($lat) {
                            //         // Add export functionality here
                            //         // Example: Generate PDF or Excel export
                                    
                        //             Notification::make()
                        //                 ->title('Report Ready!')
                        //                 ->success()
                        //                 ->body('Financial report has been generated.')
                        //                 ->send();
                        //         }),
                        ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);

        return $table;
    }
}