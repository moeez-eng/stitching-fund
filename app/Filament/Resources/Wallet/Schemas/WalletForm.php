<?php

namespace App\Filament\Resources\Wallet\Forms;

use Filament\Forms;
use Filament\Forms\Form;
use App\Models\User;

class WalletForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Select::make('investor_id')
                ->label('Investor')
                ->options(fn () => User::where('role', 'Investor')->pluck('name', 'id'))
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->required()
                ->prefix('$'),
            Forms\Components\Select::make('slip_type')
                ->options([
                    'bank_transfer' => 'Bank Transfer',
                    'cash' => 'Cash',
                    'check' => 'Check',
                ])
                ->required(),
            Forms\Components\FileUpload::make('slip_path')
                ->label('Deposit Slip')
                ->directory('wallet-slips')
                ->downloadable()
                ->openable()
                ->required(),
            Forms\Components\TextInput::make('reference')
                ->label('Reference/Check #')
                ->maxLength(255),
            Forms\Components\DatePicker::make('deposited_at')
                ->label('Deposit Date')
                ->required()
                ->default(now()),
        ];
    }
}
