<?php

namespace App\Filament\Resources\Lats\Pages;

use App\Filament\Resources\Lats\LatsResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Placeholder;

class LatDetails extends ViewRecord
{
    protected static string $resource = LatsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Action::make('update_profit_settings')
                ->label('Update Profit Settings')
                ->icon('heroicon-o-cog-6-tooth')
                ->form([
                    TextInput::make('initial_investment')
                        ->label('Initial Investment (PKR)')
                        ->numeric()
                        ->default($this->record->initial_investment ?? 0)
                        ->required(),
                    
                    TextInput::make('pieces')
                        ->label('Total Pieces')
                        ->numeric()
                        ->default($this->record->pieces ?? 1)
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
                        ->default($this->record->profit_percentage ?? 10)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'initial_investment' => $data['initial_investment'],
                        'pieces' => $data['pieces'],
                        'profit_percentage' => $data['profit_percentage'],
                    ]);
                }),
        ];
    }

    protected function getFormSchema(): array
    {
        $lat = $this->record;
        $materials = $lat->materials;
        $expenses = $lat->expenses;
        
        // Calculate totals
        $initialInvestment = $lat->initial_investment ?? 0;
        $materialsTotal = $materials->sum('price');
        $expensesTotal = $expenses->sum('price');
        $totalInvestment = $initialInvestment + $materialsTotal + $expensesTotal;
        
        // Get profit percentage from lat record or default to 10%
        $profitPercentage = $lat->profit_percentage ?? 10;
        $profitAmount = ($totalInvestment * $profitPercentage) / 100;
        $sellingPrice = $totalInvestment + $profitAmount;
        
        // Calculate per piece cost if pieces > 0
        $pieces = $lat->pieces ?? 1;
        $costPerPiece = $pieces > 0 ? $totalInvestment / $pieces : 0;
        $sellingPricePerPiece = $pieces > 0 ? $sellingPrice / $pieces : 0;
        $profitPerPiece = $pieces > 0 ? $profitAmount / $pieces : 0;

        return [
            Placeholder::make('financial_summary_title')
                ->label('Financial Summary')
                ->content(new HtmlString('<h3 class="text-lg font-bold">Financial Summary</h3><p class="text-sm text-gray-600 mb-4">Complete cost analysis and profit calculations</p>')),

            Placeholder::make('initial_investment_display')
                ->label('Initial Investment')
                ->content(new HtmlString('<div class="font-bold text-primary">PKR ' . number_format($initialInvestment, 2) . '</div>')),
            
            Placeholder::make('materials_total_display')
                ->label('Materials Cost')
                ->content(new HtmlString('<div>PKR ' . number_format($materialsTotal, 2) . '</div>')),
            
            Placeholder::make('expenses_total_display')
                ->label('Labor/Expenses')
                ->content(new HtmlString('<div>PKR ' . number_format($expensesTotal, 2) . '</div>')),
            
            Placeholder::make('total_investment_display')
                ->label('Total Investment')
                ->content(new HtmlString('<div class="font-bold text-primary">PKR ' . number_format($totalInvestment, 2) . '</div>')),
            
            Placeholder::make('profit_percentage_display')
                ->label('Profit Percentage')
                ->content(new HtmlString('<div class="font-bold text-warning">' . $profitPercentage . '%</div>')),
            
            Placeholder::make('profit_amount_display')
                ->label('Profit Amount')
                ->content(new HtmlString('<div>PKR ' . number_format($profitAmount, 2) . '</div>')),
            
            Placeholder::make('selling_price_display')
                ->label('Selling Price')
                ->content(new HtmlString('<div class="font-bold text-success">PKR ' . number_format($sellingPrice, 2) . '</div>')),
            
            Placeholder::make('cost_per_piece_display')
                ->label('Cost Per Piece')
                ->content(new HtmlString('<div>PKR ' . number_format($costPerPiece, 2) . '</div>')),
            
            Placeholder::make('profit_per_piece_display')
                ->label('Profit Per Piece')
                ->content(new HtmlString('<div>PKR ' . number_format($profitPerPiece, 2) . '</div>')),
            
            Placeholder::make('selling_price_per_piece_display')
                ->label('Selling Price Per Piece')
                ->content(new HtmlString('<div class="font-bold text-success">PKR ' . number_format($sellingPricePerPiece, 2) . '</div>')),
        ];
    }
}
