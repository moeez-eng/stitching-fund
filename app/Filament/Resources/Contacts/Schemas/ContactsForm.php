<?php

namespace App\Filament\Resources\Contacts\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class ContactsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('name')
               ->label('Name')
               ->required()
               ->maxLength(255),
               TextInput::make('phone')
               ->label('Phone')
               ->required()
               ->maxLength(255)
               ->unique(),
               Select::make('Ctype')
                ->options([
                    'Customer' => 'Customer',
                    'Investor' => 'Investor',
                    'Employee' => 'Employee',
                ])
                ->required(),
               
            ]);
    }
}
