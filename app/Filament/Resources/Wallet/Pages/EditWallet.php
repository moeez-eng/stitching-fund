<?php

namespace App\Filament\Resources\Wallet\Pages;

use Filament\Actions;
use App\Filament\Resources\Wallet;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Wallet\WalletResource;

class EditWallet extends EditRecord
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
