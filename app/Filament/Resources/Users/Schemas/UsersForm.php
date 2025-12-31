<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;

class UsersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                    ->dehydrated(fn ($state) => filled($state)),
                Select::make('role')
                    ->options([
                        'Super Admin' => 'Super Admin',
                        'Agency Owner' => 'Agency Owner',
                        'Investor' => 'Investor',
                    ])
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('agency_owner_id', null)),
                Select::make('agency_owner_id')
                    ->label('Agency Owner')
                    ->options(function () {
                        return \App\Models\User::where('role', 'Agency Owner')
                            ->orWhere('role', 'Super Admin')
                            ->pluck('name', 'id');
                    })
                    ->visible(fn (callable $get) => $get('role') === 'Investor')
                    ->required(fn (callable $get) => $get('role') === 'Investor'),
                Forms\Components\Toggle::make('status')
                    ->label('Active')
                    ->default(true)
                    ->getStateUsing(function ($record) {
                        return $record->status === 'active';
                    })
                    ->dehydrateStateUsing(function ($state) {
                        return $state ? 'active' : 'inactive';
                    }),
            ]);
    }
}
