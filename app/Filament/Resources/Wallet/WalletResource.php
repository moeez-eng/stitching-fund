<?php

namespace App\Filament\Resources\Wallet;

use UnitEnum;
use BackedEnum;
use App\Models\User;
use Filament\Tables;
use App\Models\Wallet;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\EditAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use App\Filament\Resources\Wallet\Forms\WalletForm;
use App\Filament\Resources\Wallet\Pages\EditWallet;
use App\Filament\Resources\Wallet\Pages\ListWallets;
use App\Filament\Resources\Wallet\Pages\CreateWallet;
use App\Filament\Resources\Wallet\Tables\WalletTable;

class WalletResource extends Resource
{
    protected static ?string $model = Wallet::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    
    protected static string|UnitEnum|null $navigationGroup = 'Investment Management';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema(WalletForm::schema());
    }

    public static function table(Table $table): Table
    {
        return WalletTable::table($table);
    }

    public static function canViewAny(): bool
    {
         $user = Auth::user();
        return $user && in_array($user->role, ['Super Admin', 'Agency Owner', 'Investor']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['Super Admin', 'Agency Owner', 'Investor']);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\Wallet\Pages\ListWallets::route('/'),
            'create' => \App\Filament\Resources\Wallet\Pages\CreateWallet::route('/create'),
            'edit' => \App\Filament\Resources\Wallet\Pages\EditWallet::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Wallet';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-credit-card';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Super Admin sees all
        if ($user->role === 'Super Admin') {
            return $query;
        }
        
        // Agency Owner sees all wallets from their agency
        if ($user->role === 'Agency Owner') {
            return $query->where('agency_owner_id', $user->id);
        }
        
        // Investor sees only their own wallets
        if ($user->role === 'Investor') {
            return $query->where('investor_id', $user->id);
        }
        
        return $query->whereRaw('1 = 0');
    }
    
    public static function canCreate(): bool
    {
        return Auth::user()->role === 'Agency Owner';
    }
    
    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user || !$record) return false;
        
        // Super Admin can edit all
        if ($user->role === 'Super Admin') return true;
        
        // Agency Owner can edit wallets from their agency
        if ($user->role === 'Agency Owner' && $record->agency_owner_id === $user->id) return true;
        
        // Investors cannot edit
        return false;
    }
    
    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user || !$record) return false;
        
        // Super Admin can delete all
        if ($user->role === 'Super Admin') return true;
        
        // Agency Owner can delete wallets from their agency
        if ($user->role === 'Agency Owner' && $record->agency_owner_id === $user->id) return true;
        
        // Investors cannot delete
        return false;
    }
    
    public static function canView($record): bool
    {
        $user = Auth::user();
        if (!$user || !$record) return false;
        
        // Super Admin can view all
        if ($user->role === 'Super Admin') return true;
        
        // Agency Owner can view wallets from their agency
        if ($user->role === 'Agency Owner' && $record->agency_owner_id === $user->id) return true;
        
        // Investor can view their own wallets
        if ($user->role === 'Investor' && $record->investor_id === $user->id) return true;
        
        return false;
    }
   
}