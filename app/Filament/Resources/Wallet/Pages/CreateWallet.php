<?php

namespace App\Filament\Resources\Wallet\Pages;

use App\Filament\Resources\Wallet\WalletResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWallet extends CreateRecord
{
    protected static string $resource = WalletResource::class;
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
