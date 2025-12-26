<?php

namespace App\Filament\Resources\InvestmentPool\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\InvestmentPool;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('lat_id')
                    ->label('Lot Number')
                    ->relationship('lat', 'lat_no')
                    ->searchable(),

                Tables\Filters\Filter::make('status')
                    ->label('Fully Collected')
                    ->query(fn ($query) => $query->where('percentage_collected', '>=', 100)),

                Tables\Filters\Filter::make('partial')
                    ->label('Partially Collected')
                    ->query(fn ($query) => $query->whereBetween('percentage_collected', [0.01, 99.99])),

                Tables\Filters\Filter::make('pending')
                    ->label('Not Started')
                    ->query(fn ($query) => $query->where('percentage_collected', 0)),
            ])
            ->actions([
              EditAction::make(),
              ViewAction::make(),
              DeleteAction::make(),
            ])
            ->bulkActions([
              BulkActionGroup::make([
                  DeleteBulkAction::make(),
                ]),
            ]);
    }
}