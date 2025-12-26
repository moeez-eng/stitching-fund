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
    protected ?array $invitationData = null;

    public function mount(): void
    {
        parent::mount();
        
        // Check for invitation token in URL
        $invitationCode = request('invitation');
        
        if ($invitationCode) {
            Log::info('Mount method - Invitation code found:', ['invitationCode' => $invitationCode]);

            $this->invitation = UserInvitation::where('unique_code', $invitationCode)
                ->where('expires_at', '>', now())
                ->whereNull('accepted_at')
                ->first();

            Log::info('Mount method - Invitation query result:', ['invitation' => $this->invitation]);
            
            // Check if invitation exists but is already accepted or invalid
            if ($invitationCode && !$this->invitation) {
                $existingInvitation = UserInvitation::where('unique_code', $invitationCode)->first();
                
                if ($existingInvitation) {
                    if ($existingInvitation->accepted_at) {
                        // Invitation already used
                        \Filament\Notifications\Notification::make()
                            ->title('Invitation Already Used')
                            ->body('This invitation link has already been used to register an account.')
                            ->danger()
                            ->send();
                    } else {
                        // Invitation expired (clicked but not accepted)
                        \Filament\Notifications\Notification::make()
                            ->title('Invitation Expired')
                            ->body('This invitation link has already been used. Please contact your agency owner for a new invitation.')
                            ->danger()
                            ->send();
                    }
                } else {
                    // Invalid invitation code
                    \Filament\Notifications\Notification::make()
                        ->title('Invalid Invitation')
                        ->body('This invitation link is not valid.')
                        ->danger()
                        ->send();
                }
                
                // Redirect to regular registration
                $this->redirect(route('filament.admin.auth.register'));
                return;
            }
            
            if ($this->invitation) {
                // Store invitation data before deletion
                $this->invitationData = $this->invitation->toArray();
                
                // Delete invitation immediately (one-time use)
                $this->invitation->delete();
                
                // Set invitation to null since it's deleted
                $this->invitation = null;
                
                // Pre-fill form data with invitation details
                $this->form->fill([
                    'email' => $this->invitationData['email'],
                    'role' => 'Investor'
                ]);
                
                Log::info('Invitation deleted:', [
                    'invitation_id' => $this->invitationData['id'],
                    'email' => $this->invitationData['email']
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
        if ($this->invitationData) {
            $data['role'] = 'Investor';
            $data['email'] = $this->invitationData['email'];
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
}