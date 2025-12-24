<?php

namespace App\Filament\Resources\UserInvitations\Schemas;

use Filament\Forms;
use Filament\Schemas\Schema;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;

class UserInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('company_name')
                    ->required()
                    ->maxLength(255)
                    ->label('Company Name')
                    ->helperText('This will be part of the invitation URL'),
                Forms\Components\Select::make('role')
                    ->options([
                        'Investor' => 'Investor',
                    ])
                    ->required()
                    ->default('Investor')
                    ->disabled(),
                Forms\Components\Hidden::make('invited_by')
                    ->default(fn () => \Illuminate\Support\Facades\Auth::id()),
                Forms\Components\Hidden::make('token')
                    ->default(fn () => UserInvitation::generateToken()),
                Forms\Components\Hidden::make('unique_code')
                    ->default(fn () => UserInvitation::generateUniqueCode()),
                Forms\Components\Hidden::make('expires_at')
                    ->default(fn () => now()->addDays(7)),
            ]);
    }
}
