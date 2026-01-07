<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Models\UserInvitation;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Register as BaseRegister;

class Register extends BaseRegister
{
    // Livewire properties (must be public)
    public ?string $invitationCode = null;
    public ?array $invitationData = null;
    public ?UserInvitation $invitation = null;

    // Mount method to load invitation from URL
    public function mount(): void
    {
        parent::mount();

        $this->invitationCode = request('invitation');

        if ($this->invitationCode) {
            Log::info('Mount method - Invitation code found:', ['invitationCode' => $this->invitationCode]);

            $this->invitation = UserInvitation::where('unique_code', $this->invitationCode)
                ->first();

            Log::info('Mount method - Invitation query result:', ['invitation' => $this->invitation]);

            if ($this->invitation) {
                if ($this->invitation->status === 'pending' &&
                    $this->invitation->expires_at > now() &&
                    !$this->invitation->accepted_at
                ) {
                    // Store invitation data
                    $this->invitationData = $this->invitation->toArray();

                    // Prefill form fields
                    $this->form->fill([
                        'email' => $this->invitationData['email'],
                        'role' => 'Investor',
                        'status' => 'active',
                        'invited_by' => $this->invitationData['invited_by']
                    ]);

                    Log::info('Pending invitation found and form pre-filled:', [
                        'invitation_id' => $this->invitationData['id'],
                        'invited_by' => $this->invitationData['invited_by'],
                        'email' => $this->invitationData['email']
                    ]);
                } else {
                    $this->handleInvalidOrUsedInvitation($this->invitationCode);
                }
            } else {
                $this->handleInvalidOrUsedInvitation($this->invitationCode);
            }
        } else {
            // Direct registration for Agency Owner
            $this->form->fill([
                'role' => 'Agency Owner',
                'status' => 'inactive'
            ]);
        }
    }

    // Show notification & redirect if invitation invalid
    protected function handleInvalidOrUsedInvitation(string $invitationCode): void
    {
        $invitation = UserInvitation::where('unique_code', $invitationCode)->first();
        $message = 'Invalid invitation code.';

        if ($invitation) {
            if ($invitation->accepted_at || $invitation->status === 'accepted') {
                $message = 'This invitation has already been used.';
            } elseif ($invitation->expires_at < now()) {
                $message = 'This invitation has expired.';
                $invitation->update(['status' => 'expired']);
            } elseif ($invitation->status !== 'pending') {
                $message = 'This invitation is no longer valid.';
            }
        }

        \Filament\Notifications\Notification::make()
            ->title('Invitation Not Valid')
            ->body($message)
            ->danger()
            ->send();

        $this->redirect(route('filament.admin.auth.register'));
    }

    // Form fields
    public function form(Schema $schema): Schema
    {
        $invitation = $this->invitation;
        $isInvitation = ! empty($invitation);

        Log::info('Form method - Invitation status:', [
            'isInvitation' => $isInvitation,
            'invitationCode' => $this->invitationCode,
            'invited_by' => $invitation->invited_by ?? null
        ]);

        $components = [
            $this->getNameFormComponent(),

            $this->getEmailFormComponent()
                ->disabled($isInvitation)
                ->dehydrated(true),

            $this->getPasswordFormComponent(),

            $this->getPasswordConfirmationFormComponent(),

            TextInput::make('role')
                ->label('Role')
                ->default($isInvitation ? 'Investor' : 'Agency Owner')
                ->disabled(true)
                ->dehydrated(true)
                ->required()
                ->helperText($isInvitation
                    ? 'You are registering as an Investor via invitation link.'
                    : 'You are registering as an Agency Owner.'),
        ];

        // Add hidden invited_by field for invitation
        if ($isInvitation) {
            $components[] = TextInput::make('invited_by')
                ->default($invitation->invited_by)
                ->hidden()
                ->dehydrated(true);
        }

        return $schema
            ->schema($components)
            ->statePath('data');
    }

    // Before user is created, merge invitation data
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if ($this->invitationData) {
            return array_merge($data, [
                'email' => $this->invitationData['email'],
                'role' => 'Investor',
                'status' => 'inactive',
                'invited_by' => $this->invitationData['invited_by'] ?? null,
                'company_name' => $this->invitationData['company_name'] ?? null,
            ]);
        }

        return array_merge($data, [
            'role' => 'Agency Owner',
            'status' => 'inactive'
        ]);
    }

    // Override registration to handle invited_by and mark invitation accepted
    protected function handleRegistration(array $data): User
    {
        // Get the mutated data (includes invited_by)
        $mutatedData = $this->mutateFormDataBeforeCreate($data);
        
        // Create user manually with invited_by
        $user = User::create([
            'name' => $mutatedData['name'],
            'email' => $mutatedData['email'],
            'password' => $mutatedData['password'], // Already hashed by Filament
            'role' => $mutatedData['role'],
            'status' => $mutatedData['status'] ?? 'inactive', // Add default status
            'invited_by' => $mutatedData['invited_by'] ?? null,
            'company_name' => $mutatedData['company_name'] ?? null,
        ]);
        Log::info('Registration data:', [
        'data' => $data,
        'mutated_data' => $mutatedData,
        'invitation' => $this->invitation ? $this->invitation->toArray() : null
        ]);

        // Mark invitation accepted and link user
        if ($this->invitation) {
            $this->invitation->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'user_id' => $user->id,
            ]);
        }

        return $user;
    }
}
