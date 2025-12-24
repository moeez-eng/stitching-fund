<?php

namespace App\Filament\Resources\UserInvitation;

use BackedEnum;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use App\Models\UserInvitation;
use Illuminate\Support\Carbon;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class UserInvitationResource extends Resource
{
    protected static ?string $model = UserInvitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static ?string $navigationLabel = 'Investor Invitations';

    protected static ?string $modelLabel = 'Investor Invitation';

    protected static ?string $pluralModelLabel = 'Investor Invitations';

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Invitation Details')
                    ->schema([
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique('user_invitations', 'email', ignoreRecord: true)
                            ->label('Investor Email'),

                        Forms\Components\TextInput::make('company_name')
                            ->required()
                            ->label('Company Name'),

                        Forms\Components\Hidden::make('role')
                            ->default('Investor'),

                        Forms\Components\Hidden::make('invited_by')
                            ->default(fn() => Auth::id()),

                        Forms\Components\Hidden::make('token')
                            ->default(fn() => Str::random(32)),

                        Forms\Components\Hidden::make('unique_code')
                            ->default(fn() => strtoupper(Str::random(8))),

                        Forms\Components\DateTimePicker::make('expires_at')
                            ->required()
                            ->default(fn() => Carbon::now()->addDays(7))
                            ->label('Invitation Expires'),

                        Forms\Components\Placeholder::make('preview_link')
                            ->label('Invitation Link Preview')
                            ->content(fn($get) => route('filament.admin.auth.register') . '?invitation=' . $get('unique_code'))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')
                    ->label('Investor Email')
                    ->searchable(),

                Tables\Columns\TextColumn::make('company_name')
                    ->label('Company Name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('inviter.name')
                    ->label('Invited By')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (UserInvitation $record): string {
                        if ($record->isAccepted()) {
                            return 'Accepted';
                        } elseif ($record->isExpired()) {
                            return 'Expired';
                        } else {
                            return 'Pending';
                        }
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Accepted' => 'success',
                        'Expired' => 'danger',
                        'Pending' => 'warning',
                    }),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'expired' => 'Expired',
                    ])
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'pending') {
                            $query->whereNull('accepted_at')->where('expires_at', '>', now());
                        } elseif ($data['value'] === 'accepted') {
                            $query->whereNotNull('accepted_at');
                        } elseif ($data['value'] === 'expired') {
                            $query->where('expires_at', '<', now())->whereNull('accepted_at');
                        }
                    }),
            ])
            ->actions([
                Action::make('resend')
                    ->label('Resend Email')
                    ->icon('heroicon-o-paper-airplane')
                    ->action(function (UserInvitation $record) {
                        // TODO: Send email notification
                        // This will trigger the email sending logic
                    })
                    ->visible(fn(UserInvitation $record) => $record->isValid()),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->latest();
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\UserInvitation\Pages\ListUserInvitations::route('/'),
            'create' => \App\Filament\Resources\UserInvitation\Pages\CreateUserInvitation::route('/create'),
            'edit' => \App\Filament\Resources\UserInvitation\Pages\EditUserInvitation::route('/{record}/edit'),
        ];
    }
}
