<?php

namespace App\Filament\Resources\InvestmentPool\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\InvestmentPool;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class InvestmentPoolTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lat.lat_no')
                    ->label('Lot Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('design_name')
                    ->label('Design Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_required')
                    ->label('Amount Required')
                    ->money('PKR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('number_of_partners')
                    ->label('Partners')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_collected')
                    ->label('Total Collected')
                    ->money('PKR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('percentage_collected')
                    ->label('Collected %')
                    ->badge()
                    ->color(fn (InvestmentPool $record): string => 
                        $record->percentage_collected >= 100 ? 'success' : 
                        ($record->percentage_collected >= 50 ? 'warning' : 'danger')
                    )
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('remaining_amount')
                    ->label('Remaining')
                    ->money('PKR')
                    ->sortable()
                    ->color(fn (InvestmentPool $record): string => 
                        $record->remaining_amount > 0 ? 'warning' : 'success'
                    ),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lat_id')
                    ->label('Lot Number')
                    ->relationship('lat', 'lat_no')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Collection Status')
                    ->options([
                        'completed' => 'Fully Collected',
                        'partial' => 'Partially Collected',
                        'pending' => 'Not Started',
                    ])
                    ->query(fn ($query) => $query->where('percentage_collected', '>=', 100), 'completed')
                    ->query(fn ($query) => $query->where('percentage_collected', '>', 0)->where('percentage_collected', '<', 100), 'partial')
                    ->query(fn ($query) => $query->where('percentage_collected', '=', 0), 'pending'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}