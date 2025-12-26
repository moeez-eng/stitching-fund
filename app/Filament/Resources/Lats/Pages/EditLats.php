<?php

namespace App\Filament\Resources\Lats\Pages;

use App\Filament\Resources\Lats\LatsResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLats extends EditRecord
{
    protected static string $resource = LatsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn ($record) => $record !== null),
        ];
    }
     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}