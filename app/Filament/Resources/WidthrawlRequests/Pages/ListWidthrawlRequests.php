<?php

namespace App\Filament\Resources\WidthrawlRequests\Pages;

use App\Filament\Resources\WidthrawlRequests\WidthrawlRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWidthrawlRequests extends ListRecords
{
    protected static string $resource = WidthrawlRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
