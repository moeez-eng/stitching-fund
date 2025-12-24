<?php

namespace App\Filament\Resources\UserInvitation\Pages;

use App\Filament\Resources\UserInvitation\UserInvitationResource;
use App\Models\UserInvitation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\InvestorInvitationMail;

class CreateUserInvitation extends CreateRecord
{
    protected static string $resource = UserInvitationResource::class;

    protected function afterCreate(): void
    {
        // Send invitation email after creating the invitation
        $invitation = $this->record;
        
        try {
            Mail::to($invitation->email)->send(new InvestorInvitationMail($invitation));
        } catch (\Exception $e) {
            // Log error but don't fail the creation
            Log::error('Failed to send invitation email: ' . $e->getMessage());
        }
    }

    protected function getRedirectUrl(): string
    {
        return UserInvitationResource::getUrl('index');
    }
}
