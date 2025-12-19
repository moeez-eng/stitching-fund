<?php

namespace App\Filament\Resources\Lats\Pages;

use App\Filament\Resources\Lats\LatsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLats extends CreateRecord
{
    protected static string $resource = LatsResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
