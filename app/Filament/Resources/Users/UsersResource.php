<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUsers;
use App\Filament\Resources\Users\Pages\EditUsers;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\RegisterUser;
use App\Filament\Resources\Users\Schemas\UsersForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use App\Models\Contact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UsersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'users';

    public static function form(Schema $schema): Schema
    {
        return UsersForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUsers::route('/create'),
            'edit' => EditUsers::route('/{record}/edit'),
           
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'Super Admin';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && $user->role === 'Super Admin';
    }
    
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->forCurrentUser();
    }
    
    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // Debug logging to check what data is being submitted
        \Illuminate\Support\Facades\Log::info('User form data before save: ', $data);
        
        // Ensure agency_owner_id is properly handled
        if (isset($data['role']) && $data['role'] === 'Investor') {
            // If role is Investor but no agency_owner_id is set, log an error
            if (!isset($data['agency_owner_id']) || empty($data['agency_owner_id'])) {
                \Illuminate\Support\Facades\Log::error('Investor created without agency_owner_id');
            }
        } else {
            // If role is not Investor, clear agency_owner_id
            $data['agency_owner_id'] = null;
        }
        
        \Illuminate\Support\Facades\Log::info('User form data after processing: ', $data);
        return $data;
    }
}
