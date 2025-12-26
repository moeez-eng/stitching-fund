<?php

namespace App\Filament\Resources\InvestmentPool\Pages;

use App\Filament\Resources\InvestmentPool\InvestmentPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvestmentPool extends EditRecord
{
    protected static string $resource = InvestmentPoolResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
