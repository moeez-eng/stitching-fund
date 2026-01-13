<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\User;

class CreateUsers extends CreateRecord
{
    protected static string $resource = UsersResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default status to inactive for new users, except Super Admin
        if ($data['role'] === 'Super Admin') {
            $data['status'] = 'active';
        } else {
            $data['status'] = 'inactive';
        }
        
        return $data;
    }

   
}