<?php

namespace App\Filament\Resources\UserInvitation\Pages;

use App\Filament\Resources\UserInvitation\UserInvitationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserInvitation extends EditRecord
{
    protected static string $resource = UserInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record !== null),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return UserInvitationResource::getUrl('index');
    }
}
