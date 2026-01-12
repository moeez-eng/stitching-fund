<?php

namespace App\Filament\Resources\WidthrawlRequests\Pages;

use App\Filament\Resources\WidthrawlRequests\WidthrawlRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWidthrawlRequest extends EditRecord
{
    protected static string $resource = WidthrawlRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
