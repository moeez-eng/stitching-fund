<?php

namespace App\Filament\Resources\Lats\RelationManagers;

use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\Lats\LatsResource;
use Filament\Resources\RelationManagers\RelationManager;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    protected static ?string $relatedResource = LatsResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('material')
                    ->required()
                    ->maxLength(255),

                TextInput::make('colour')
                    ->maxLength(255),

                Select::make('unit')
                    ->options([
                        'Yards' => 'Yards',
                        'Roll' => 'Roll',
                        'Packet' => 'Packet',
                        'Cone' => 'Cone',
                    ])
                    ->required(),

                TextInput::make('rate')
                    ->numeric()
                    ->required(),

                TextInput::make('quantity')
                    ->numeric()
                    ->required(),
                    
                TextInput::make('price')
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
            ]);
    } 
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('material')->searchable(),
                TextColumn::make('colour'),
                TextColumn::make('unit'),
                TextColumn::make('rate')->money('PKR'),
                TextColumn::make('quantity'),
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
