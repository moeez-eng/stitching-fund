<?php

namespace App\Filament\Resources\Lats\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\RelationManagers\RelationManager;

class ExpenseRelationManager extends RelationManager
{
    protected static string $relationship = 'expenses';

    public function form(Schema $schema): Schema
    {
        return $schema
        ->schema([
            DatePicker::make('dated')
                ->required(),

            TextInput::make('lat_id')
                ->label('Lat ID')
                ->disabled(),

            TextInput::make('labour_type')
                ->numeric()
                ->required(),
            TextInput::make('unit')
                ->numeric()
                ->required(),
            TextInput::make('rate')
                ->numeric()
                ->required(),
            TextInput::make('pieces')
                ->numeric()
                ->required(),
            TextInput::make('price')
                ->numeric()
                ->required(),

        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('dated')->date(),
                TextColumn::make('labour_type'),
                TextColumn::make('unit'),
                TextColumn::make('rate'),
                TextColumn::make('pieces'),
                TextColumn::make('price')->money('PKR'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
