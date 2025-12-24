<?php

namespace App\Filament\Resources\UserInvitations;

use BackedEnum;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\UserInvitation;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\UserInvitations\Pages\EditUserInvitation;
use App\Filament\Resources\UserInvitations\Pages\ListUserInvitations;
use App\Filament\Resources\UserInvitations\Pages\CreateUserInvitation;
use App\Filament\Resources\UserInvitations\Schemas\UserInvitationForm;
use App\Filament\Resources\UserInvitations\Tables\UserInvitationsTable;

class UserInvitationResource extends Resource
{
    protected static ?string $model = UserInvitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Envelope;

    protected static ?string $navigationLabel = 'Investor Invitations';

    protected static ?string $modelLabel = 'Invitation';

    protected static ?string $pluralModelLabel = 'Invitations';

    public static function form(Schema $schema): Schema
    {
        return UserInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserInvitationsTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery(); // Global scope handles privacy filtering
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUserInvitations::route('/'),
            'create' => CreateUserInvitation::route('/create'),
            'edit' => EditUserInvitation::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['Agency Owner', 'Super Admin']);
    }
}
