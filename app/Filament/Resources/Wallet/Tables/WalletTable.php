<?php

namespace App\Filament\Resources\Wallet\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class WalletTable
{
    public static function configure(Table $table): Table
    {
        return self::table($table);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('investor.name')
                    ->label('Investor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->money('USD')
                    ->money('PKR')
                    ->sortable(),
                ImageColumn::make('slip_path')
                    ->disk('public'),
                TextColumn::make('deposited_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}