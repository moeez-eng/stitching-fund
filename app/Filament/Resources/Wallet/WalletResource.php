<?php

namespace App\Filament\Resources\Wallet;

use BackedEnum;
use UnitEnum;
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
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-wallet';
    
    protected static ?string $navigationGroup = 'Investment Management';

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
        return $user && in_array($user->role, ['Super Admin', 'Agency Owner']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['Super Admin', 'Agency Owner']);
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        
        if ($user->role === 'Agency Owner') {
            return $query->where('agency_owner_id', $user->id);
        }
        
        if ($user->role === 'Investor') {
            return $query->where('investor_id', $user->id);
        }
        
        return $query;
    }
    
    public static function canCreate(): bool
    {
        return Auth::user()->role === 'Agency Owner';
    }
    
   
}