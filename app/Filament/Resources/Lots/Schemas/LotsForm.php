<?php

namespace App\Filament\Resources\Lots\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;

class LotsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('lot_no')
                    ->label('Lot No')
                    ->required()
                    ->maxLength(255),
                TextInput::make('design_name')
                    ->label('Design Name')
                    ->maxLength(255),
                TextInput::make('coustmer_name')
                    ->label('Customer Name')
                    ->maxLength(255),
            ]);
    }
}
