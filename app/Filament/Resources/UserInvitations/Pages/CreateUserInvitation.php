<?php

namespace App\Filament\Resources\UserInvitations\Pages;

use App\Filament\Resources\UserInvitations\UserInvitationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserInvitation extends CreateRecord
{
    protected static string $resource = UserInvitationResource::class;
}
