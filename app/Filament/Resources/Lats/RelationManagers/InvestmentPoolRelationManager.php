<?php

namespace App\Filament\Resources\Lats\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Illuminate\Support\HtmlString;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;
use App\Filament\Resources\InvestmentPool\Tables\InvestmentPoolTable;

class InvestmentPoolRelationManager extends RelationManager
{
    protected static string $relationship = 'investmentPools';

    protected static ?string $title = 'Investment Pools';

    public function table(Table $table): Table
    {
        return InvestmentPoolTable::configure($table)
            ->headerActions([
                Action::make('create')
                    ->label('New Investment Pool')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->url(fn () => route('filament.admin.resources.investment-pool.investment-pools.create', [
                        'lat_id' => $this->getOwnerRecord()->id
                    ])),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.investment-pool.investment-pools.view', $record)),
                EditAction::make()
                    ->url(fn ($record) => route('filament.admin.resources.investment-pool.investment-pools.edit', $record)),
            ])
            
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->poll(10);
    }
}