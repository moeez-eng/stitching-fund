<?php

namespace App\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Component;
use Filament\Auth\Pages\Register as BaseRegister;
use Filament\Forms\Components\TextInput;

class Register extends BaseRegister
{
    protected ?UserInvitation $invitation = null;

    public function mount(): void
    {
        parent::mount();
        
        // Check for invitation token in URL
        $invitationCode = request('invitation');
        
        if ($invitationCode) {
            Log::info('Mount method - Invitation code found:', ['invitationCode' => $invitationCode]);

            $this->invitation = UserInvitation::where('unique_code', $invitationCode)
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->first();

            Log::info('Mount method - Invitation query result:', ['invitation' => $this->invitation]);
            
            if ($this->invitation) {
                // Store invitation data in session to persist between method calls
                session(['invitation_data' => $this->invitation]);
                
                // Pre-fill form data with invitation details
                $this->form->fill([
                    'email' => $this->invitation->email,
                    'role' => 'Investor'
                ]);
            }
        } else {
            // Direct registration - set default role
            $this->form->fill([
                'role' => 'Agency Owner'
            ]);
        }
    }

    public function form(Schema $schema): Schema
    {
        // Check if this is an invitation registration by directly checking the URL parameter
        $invitationCode = request('invitation');
        $isInvitation = !empty($invitationCode) && 
                       UserInvitation::where('unique_code', $invitationCode)
                                   ->whereNull('accepted_at')
                                   ->where('expires_at', '>', now())
                                   ->exists();
        
        // Debug: Log the invitation status
        Log::info('Form method - Invitation status:', [
            'isInvitation' => $isInvitation,
            'invitationCode' => $invitationCode
        ]);
        
        $components = [
            $this->getNameFormComponent(),
            
            $this->getEmailFormComponent()
                ->disabled($isInvitation)
                ->dehydrated(true), // Ensure value is included even when disabled
            
            $this->getPasswordFormComponent(),
            
            $this->getPasswordConfirmationFormComponent(),
            
            TextInput::make('role')
                ->label('Role')
                ->default($isInvitation ? 'Investor' : 'Agency Owner')
                ->disabled(true) // Always disabled
                ->dehydrated(true) // Ensure value is included even when disabled
                ->required()
                ->helperText($isInvitation 
                    ? 'You are registering as an Investor via invitation link.' 
                    : 'You are registering as an Agency Owner.'),
        ];

        return $schema
            ->schema($components)
            ->statePath('data');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure role is set correctly based on invitation
        if ($this->invitation) {
            $data['role'] = 'Investor';
            $data['email'] = $this->invitation->email;
        } else {
            $data['role'] = 'Agency Owner';
        }
        
        // Set new user status to inactive, except Super Admin
        if ($data['role'] === 'Super Admin') {
            $data['status'] = 'active';
        } else {
            $data['status'] = 'inactive';
        }
        
        Log::info('Registration data before create:', $data);
        
        return $data;
    }

    protected function handleRegistration(array $data): \Illuminate\Database\Eloquent\Model
    {
        $user = parent::handleRegistration($data);
        
        // Mark invitation as accepted if it exists
        if ($this->invitation) {
            $this->invitation->update([
                'accepted_at' => now(),
            ]);
            
            Log::info('Invitation accepted:', [
                'invitation_id' => $this->invitation->id,
                'user_id' => $user->id
            ]);
        }
        
        return $user;
    }
}