<?php

namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Illuminate\Support\Facades\Request;
use Filament\Schemas\Components\Component;
use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    protected ?UserInvitation $invitation = null;

    public function mount(): void
    {
        parent::mount();
        
        // Check for invitation token in URL
        $invitationCode = request('invitation');
        Log::info('Invitation code from URL: ' . $invitationCode);
        
        if ($invitationCode) {
            $this->invitation = UserInvitation::where('unique_code', $invitationCode)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->first();
            
            Log::info('Invitation found: ' . ($this->invitation ? 'Yes' : 'No'));
            
            if ($this->invitation) {
                // Pre-fill email from invitation
                $this->form->fill(['email' => $this->invitation->email]);
                Log::info('Email pre-filled: ' . $this->invitation->email);
            }
        }
    }
    public function form(Schema $schema): Schema
    {
        // Check invitation directly in form method
        $invitationCode = request('invitation');
        $invitation = $invitationCode ? UserInvitation::where('unique_code', $invitationCode)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first() : null;
        
        Log::info('Form method - invitation exists: ' . ($invitation ? 'Yes' : 'No'));
        
        $components = [
            $this->getNameFormComponent(),
            $this->getEmailFormComponent()
                ->disabled($invitation !== null),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ];

        return $schema
            ->schema($components)
            ->statePath('data');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Check invitation directly
        $invitationCode = request('invitation');
        $invitation = $invitationCode ? UserInvitation::where('unique_code', $invitationCode)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first() : null;
            
        // Set role automatically based on invitation presence
        if ($invitation) {
            $data['role'] = 'Investor';
            $data['email'] = $invitation->email;
        } else {
            $data['role'] = 'Agency Owner';
        }
        
        return $data;
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        $user = parent::handleRegistration($data);
        
        // Mark invitation as accepted if using invitation
        if ($this->invitation) {
            $this->invitation->update([
                'accepted_at' => now(),
            ]);
        }
        
        return $user;
    }
}
