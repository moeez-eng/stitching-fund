<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UsersResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

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
        
        // Auto-set agency_owner_id for Investor users if not provided
        if ($data['role'] === 'Investor' && !isset($data['agency_owner_id'])) {
            $currentUser = Auth::user();
            if ($currentUser && ($currentUser->role === 'Agency Owner' || $currentUser->role === 'Super Admin')) {
                $data['agency_owner_id'] = $currentUser->id;
            }
        }
        
        return $data;
    }
}
