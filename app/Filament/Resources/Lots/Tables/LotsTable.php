<?php

namespace App\Filament\Resources\Lots\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;

class LotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
              ->columns([
                TextColumn::make('lot_no')
                    ->label('Lot Number')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('design.name')
                    ->label('Design')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
             ->defaultSort('id', 'desc')  // This line ensures latest lots appear first
            ->filters([
                SelectFilter::make('design')
                    ->label('Design')
                    ->relationship('design', 'name')
                    ->searchable(),
                SelectFilter::make('customer')
                    ->label('Customer')
                    ->relationship('customer', 'name')
                    ->searchable(),
                Filter::make('lot_no')
                    ->form([
                        TextInput::make('lot_no')
                            ->label('Lot Number')
                            ->numeric(),
                    ])
                    ->query(function ($query, array $data) {
                        return $query->when(
                            $data['lot_no'],
                            fn ($query, $lotNo) => $query->where('lot_no', $lotNo)
                        );
                    })
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url('/lotview'),
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
