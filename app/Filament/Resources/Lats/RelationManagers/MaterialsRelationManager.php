<?php

namespace App\Filament\Resources\Lats\RelationManagers;

use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\RelationManagers\RelationManager;

class MaterialsRelationManager extends RelationManager
{
    protected static string $relationship = 'materials';

    protected static ?string $recordTitleAttribute = 'material';
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('material')->required(),
                Forms\Components\TextInput::make('colour')->required(),
                Forms\Components\TextInput::make('unit')->required(),
                Forms\Components\TextInput::make('rate')
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $quantity = $get('quantity');
                        if ($quantity) {
                            $set('price', $state * $quantity);
                        }
                    }),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->numeric()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $rate = $get('rate');
                        if ($rate) {
                            $set('price', $state * $rate);
                        }
                    }),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->dehydrated()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('material')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('colour')
                    ->searchable(),
                TextColumn::make('unit'),
                TextColumn::make('rate')
                    ->money('PKR')
                    ->sortable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money('PKR')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Add Material')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['price'] = $data['rate'] * $data['quantity'];
                        return $data;
                    }),
                AttachAction::make(),
            ])
            ->actions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['price'] = $data['rate'] * $data['quantity'];
                        return $data;
                    }),
                DetachAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
}
}

