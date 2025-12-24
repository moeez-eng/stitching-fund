<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use App\Models\UserInvitation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;

class AcceptInvitation extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope-open';
    
    protected string $view = 'filament.pages.accept-invitation';

    public ?UserInvitation $invitation = null;

    protected function getActions(): array
    {
        return [
            \Filament\Actions\Action::make('create')
                ->label('Accept Invitation & Create Account')
                ->action('create')
                ->icon('heroicon-o-check'),
        ];
    }

    public function mount($companySlug = null, $uniqueCode = null)
    {
        if (!$companySlug || !$uniqueCode) {
            Notification::make()
                ->title('Invalid invitation link')
                ->danger()
                ->send();
            
            $this->redirect('/admin/login');
            return;
        }

        $this->invitation = UserInvitation::where('unique_code', $uniqueCode)->first();

        if (!$this->invitation) {
            Notification::make()
                ->title('Invitation not found')
                ->danger()
                ->send();
            
            $this->redirect('/admin/login');
            return;
        }

        // Validate company slug matches
        $expectedSlug = Str::slug($this->invitation->company_name);
        if ($companySlug !== $expectedSlug) {
            Notification::make()
                ->title('Invalid invitation link')
                ->danger()
                ->send();
            
            $this->redirect('/admin/login');
            return;
        }

        if ($this->invitation->isAccepted()) {
            Notification::make()
                ->title('This invitation has already been accepted')
                ->warning()
                ->send();
            
            $this->redirect('/admin/login');
            return;
        }

        if ($this->invitation->isExpired()) {
            Notification::make()
                ->title('This invitation has expired')
                ->danger()
                ->send();
            
            $this->redirect('/admin/login');
            return;
        }

        $this->form->fill([
            'email' => $this->invitation->email,
            'role' => $this->invitation->role,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Investment Invitation')
                    ->description('You have been invited to join as an Investor')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->disabled()
                            ->label('Email Address'),
                        TextInput::make('name')
                            ->required()
                            ->label('Full Name')
                            ->maxLength(255),
                        TextInput::make('password')
                            ->password()
                            ->required()
                            ->label('Password')
                            ->minLength(8),
                        TextInput::make('password_confirmation')
                            ->password()
                            ->required()
                            ->label('Confirm Password')
                            ->same('password'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        try {
            $data = $this->form->getState();

            // Check if user already exists
            if (User::where('email', $this->invitation->email)->exists()) {
                Notification::make()
                    ->title('An account with this email already exists')
                    ->danger()
                    ->send();
                return;
            }

            // Create user with agency owner association
            $user = User::create([
                'name' => $data['name'],
                'email' => $this->invitation->email,
                'password' => Hash::make($data['password']),
                'role' => $this->invitation->role,
                'status' => 'active',
                'invited_by' => $this->invitation->invited_by,
                'company_name' => $this->invitation->company_name,
            ]);

            // Mark invitation as accepted
            $this->invitation->update([
                'accepted_at' => now(),
            ]);

            // Log the user in
            Auth::login($user);

            Notification::make()
                ->title('Welcome! Your account has been created successfully.')
                ->success()
                ->send();

            $this->redirect('/admin');
        } catch (Halt $exception) {
            return;
        } catch (\Exception $e) {
            Notification::make()
                ->title('An error occurred')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected static ?string $title = 'Accept Invitation';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hide from navigation menu
    }
}
