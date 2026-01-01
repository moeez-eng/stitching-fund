<?php

namespace App\Filament\Resources\UserInvitation\Pages;

use App\Filament\Resources\UserInvitation\UserInvitationResource;
use App\Models\UserInvitation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\InvestorInvitationMail;
use Illuminate\Support\Facades\Auth;

class CreateUserInvitation extends CreateRecord
{
    protected static string $resource = UserInvitationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure the invited_by field is set to the current user's ID
        $userId = Auth::id();
        $data['invited_by'] = $userId;
        
        // Debug: Log the data being saved
        Log::info('Creating invitation with data:', [
            'invited_by' => $userId,
            'email' => $data['email'] ?? 'no-email',
            'all_data' => $data
        ]);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Send invitation email after creating the invitation
        $invitation = $this->record;
        
        try {
            Mail::to($invitation->email)->send(new InvestorInvitationMail($invitation));
            \Filament\Notifications\Notification::make()
                ->title('Invitation sent successfully')
                ->body('Email sent to ' . $invitation->email)
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Log error but don't fail the creation
            Log::error('Failed to send invitation email: ' . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('Failed to send invitation')
                ->body('Error: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return UserInvitationResource::getUrl('index');
    }
}
