<?php

namespace App\Filament\Resources\Lats\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;

class LatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
               TextColumn::make('lat_no')
               ->label('Lat No')
               ->searchable(),
               TextColumn::make('design_name')
               ->label('Design')
               ->searchable(),
               TextColumn::make('customer_name')
               ->label('Customer')
               ->searchable(),
            ])
            ->filters([
                SelectFilter::make('design')
                    ->label('Design')
                    ->relationship('design', 'name')
                    ->searchable(),
                SelectFilter::make('customer')
                    ->label('Customer')
                    ->relationship('customer', 'name',)
                    ->searchable(),
                Filter::make('lat_no')
                    ->form([
                        TextInput::make('lat_no')
                            ->label('Lat No')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['lat_no'],
                            fn ($query, $latNo) => $query->where('lat_no', $latNo)
                        );
                    })
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
