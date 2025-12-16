<?php

namespace App\Filament\Resources\Lots\Pages;

use App\Filament\Resources\Lots\LotsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLots extends CreateRecord
{
    protected static string $resource = LotsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
