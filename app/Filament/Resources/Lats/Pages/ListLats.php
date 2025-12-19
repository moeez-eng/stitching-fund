<?php

namespace App\Filament\Resources\Lats\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\Lats\LatsResource;

class ListLats extends ListRecords
{
    protected static string $resource = LatsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
   

protected function getTableActions(): array
{
    return [
        Action::make('open')
            ->label('Open')
            ->url(fn ($record) => static::getResource()::getUrl('view', ['record' => $record]))
    ];
}

}