<?php

namespace App\Filament\Resources\Lats\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;

class SummaryRelationManager extends RelationManager
{
    protected static ?string $relationshipTitle = 'Financial Summary';

    protected static string $relationship = 'summary';

    public function table(Table $table): Table
    {
        $lat = $this->getOwnerRecord();
        $materials = $lat->materials;
        $expenses = $lat->expenses;
        
        // Get initial investment from lat record or default to 0
        $initialInvestment = $lat->initial_investment ?? 0;

        // Calculate totals
        $materialsTotal = $materials->sum('price');
        $expensesTotal = $expenses->sum('price');
        $totalInvestment = $materialsTotal + $expensesTotal;
        
        // Get profit percentage from lat record or default to 10%
        $profitPercentage = $lat->profit_percentage ?? 10;
        $profitAmount = ($totalInvestment * $profitPercentage) / 100;
        $sellingPrice = $totalInvestment + $profitAmount;
        
        // Calculate per piece cost if pieces > 0
        $pieces = $lat->pieces ?? 1;
        $costPerPiece = $pieces > 0 ? $totalInvestment / $pieces : 0;
        $sellingPricePerPiece = $pieces > 0 ? $sellingPrice / $pieces : 0;
        $profitPerPiece = $pieces > 0 ? $profitAmount / $pieces : 0;

        // Create or update summary records
        $summaryData = [
            ['type' => 'Initial Investment', 'amount' => $initialInvestment],
            ['type' => 'Materials Cost', 'amount' => $materialsTotal],
            ['type' => 'Labor/Expenses', 'amount' => $expensesTotal],
            ['type' => 'Total Cost', 'amount' => $totalInvestment],
            ['type' => 'Profit Percentage', 'amount' => $profitPercentage],
            ['type' => 'Profit Amount', 'amount' => $profitAmount],
            ['type' => 'Selling Price', 'amount' => $sellingPrice],
            ['type' => 'Cost Per Piece', 'amount' => $costPerPiece],
            ['type' => 'Profit Per Piece', 'amount' => $profitPerPiece],
            ['type' => 'Selling Price Per Piece', 'amount' => $sellingPricePerPiece],
        ];

        // Clear existing summaries and create new ones
        $lat->summaries()->delete();
        foreach ($summaryData as $data) {
            $lat->summaries()->create($data);
        }

        return $table
            ->heading('Financial Summary')
            ->description('Complete cost analysis and profit calculations')
            ->columns([
                TextColumn::make('type')
                    ->label('Description')
                    ->formatStateUsing(function ($state, $record, $rowLoop) {
                        $isBold = in_array($rowLoop->index, [3, 6, 9]);
                        $class = $isBold ? 'font-bold text-primary' : '';
                        return new HtmlString("<div class='{$class}'>{$state}</div>");
                    }),
                    
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(function ($state, $record, $rowLoop) {
                        $isBold = in_array($rowLoop->index, [3, 6, 9]);
                        $isPercentage = $rowLoop->index === 4;
                        
                        if ($isPercentage) {
                            return new HtmlString("<div class='text-right font-bold text-warning'>{$state}%</div>");
                        }
                        
                        $class = $isBold ? 'font-bold text-success' : '';
                        $formattedAmount = number_format($state, 2);
                        
                        return new HtmlString("<div class='text-right {$class}'>PKR {$formattedAmount}</div>");
                    }),
            ])
            ->paginated(false)
            ->headerActions([
                Action::make('update_profit_settings')
                    ->label('Update Profit Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->form([
                        TextInput::make('initial_investment')
                            ->label('Initial Investment (PKR)')
                            ->numeric()
                            ->default($lat->initial_investment ?? 0)
                            ->required(),
                        
                        TextInput::make('pieces')
                            ->label('Total Pieces')
                            ->numeric()
                            ->default($lat->pieces ?? 1)
                            ->required(),
                        
                        Select::make('profit_percentage')
                            ->label('Profit Percentage')
                            ->options([
                                '5' => '5%',
                                '10' => '10%',
                                '15' => '15%',
                                '20' => '20%',
                                '25' => '25%',
                                '30' => '30%',
                            ])
                            ->default($lat->profit_percentage ?? 10)
                            ->required(),
                    ])
                    ->action(function (array $data) use ($lat) {
                        $lat->update([
                            'initial_investment' => $data['initial_investment'],
                            'pieces' => $data['pieces'],
                            'profit_percentage' => $data['profit_percentage'],
                        ]);
                    }),
            ])
            ->filters([])
            ->actions([])
            ->bulkActions([]);
    }
}