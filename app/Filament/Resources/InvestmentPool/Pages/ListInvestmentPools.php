<?php

namespace App\Filament\Resources\InvestmentPool\Pages;

use App\Filament\Resources\InvestmentPool\InvestmentPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvestmentPools extends ListRecords
{
    protected static string $resource = InvestmentPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
