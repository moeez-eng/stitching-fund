<?php

namespace App\Filament\Resources\Lots\Pages;

use App\Filament\Resources\Lots\LotsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLots extends EditRecord
{
    protected static string $resource = LotsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
