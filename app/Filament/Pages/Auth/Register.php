<?php

namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Component;
use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                Select::make('role')
                    ->options([
                        'User' => 'User',
                        'Investor' => 'Investor',
                        'Agency Owner' => 'Agency Owner',
                        
                    ])
                    ->default('User')
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data;
    }
}
