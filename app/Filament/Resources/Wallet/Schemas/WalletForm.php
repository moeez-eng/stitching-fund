<?php

namespace App\Filament\Resources\Wallet\Forms;

use Filament\Forms;
use Filament\Forms\Form;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class WalletForm
{
    public static function schema(): array
    {
        return [
            Forms\Components\Select::make('investor_id')
                ->label('Investor')
                ->options(function () {
                    $user = Auth::user();
                    if ($user->role === 'Super Admin') {
                        return User::where('role', 'Investor')->pluck('name', 'id');
                    } elseif ($user->role === 'Agency Owner') {
                        return User::where('role', 'Investor')
                            ->where('agency_owner_id', $user->id)
                            ->pluck('name', 'id');
                    }
                    return [];
                })
                ->getSearchResultsUsing(function (string $search) {
                    $user = Auth::user();
                    if ($user->role === 'Super Admin') {
                        return User::where('role', 'Investor')
                            ->where('name', 'like', "%{$search}%")
                            ->pluck('name', 'id');
                    } elseif ($user->role === 'Agency Owner') {
                        return User::where('role', 'Investor')
                            ->where('agency_owner_id', $user->id)
                            ->where('name', 'like', "%{$search}%")
                            ->pluck('name', 'id');
                    }
                    return [];
                })
                ->required()
                ->searchable(),
            Forms\Components\TextInput::make('amount')
                ->numeric()
                ->required(),
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
                ->openable(),
            Forms\Components\TextInput::make('reference')
                ->label('Reference/Check #')
                ->maxLength(255),
            Forms\Components\DatePicker::make('deposited_at')
                ->label('Deposit Date')
                ->default(now()),
        ];
    }
}
