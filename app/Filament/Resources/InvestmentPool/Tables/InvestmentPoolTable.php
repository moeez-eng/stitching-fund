<?php

namespace App\Filament\Resources\InvestmentPool\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\InvestmentPool;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;

class InvestmentPoolTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lat.lat_no')
                    ->label('Lot Number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('design_name')
                    ->label('Design Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount_required')
                    ->label('Amount Required')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('number_of_partners')
                    ->label('Partners')
                    ->badge()
                    ->color('primary')
                    ->sortable(),


                TextColumn::make('total_collected')
                    ->label('Total Collected')
                    ->money('PKR')
                    ->sortable(),

                TextColumn::make('percentage_collected')
                    ->label('Collected %')
                    ->badge()
                    ->color(fn (InvestmentPool $record): string => 
                        $record->percentage_collected >= 100 ? 'success' : 
                        ($record->percentage_collected >= 50 ? 'warning' : 'danger')
                    )
                    ->formatStateUsing(fn ($state) => number_format($state, 2) . '%')
                    ->sortable(),

                TextColumn::make('remaining_amount')
                    ->label('Remaining')
                    ->money('PKR')
                    ->sortable()
                    ->color(fn (InvestmentPool $record): string => 
                        $record->remaining_amount > 0 ? 'warning' : 'success'
                    ),
               
                TextColumn::make('created_at')
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
              ViewAction::make(),
              EditAction::make(),
              DeleteAction::make(),
            ])
            ->bulkActions([]);
    }
}