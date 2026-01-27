<?php

namespace App\Filament\Resources\UserInvitation\Schemas;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Schema;

class UserinvitationForm 
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invitation Details')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique('user_invitations', 'email', ignoreRecord: true)
                            ->label('Investor Email'),

                        TextInput::make('company_name')
                            ->required()
                            ->label('Company Name'),

                        Hidden::make('role')
                            ->default('Investor'),

                        Hidden::make('invited_by')
                            ->default(fn() => Auth::id()),

                        Hidden::make('token')
                            ->default(fn() => Str::random(32)),

                        Hidden::make('unique_code')
                            ->default(fn() => strtoupper(Str::random(8))),

                        DateTimePicker::make('expires_at')
                            ->required()
                            ->default(fn() => Carbon::now()->addDays(7))
                            ->label('Invitation Expires'),
                    ])
                    ->columns(2),
            ]);
    }


}