<?php

namespace App\Filament\Resources\UserInvitations\Pages;

use App\Filament\Resources\UserInvitations\UserInvitationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserInvitation extends EditRecord
{
    protected static string $resource = UserInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
