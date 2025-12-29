<?php

namespace App\Filament\Resources\Wallet\Pages;

use Filament\Actions;
use App\Filament\Resources\Wallet;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Wallet\WalletResource;

class ListWallets extends ListRecords
{
    protected static string $resource = WalletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
