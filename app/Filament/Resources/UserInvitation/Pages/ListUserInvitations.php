<?php

namespace App\Filament\Resources\UserInvitation\Pages;

use App\Filament\Resources\UserInvitation\UserInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserInvitations extends ListRecords
{
    protected static string $resource = UserInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
