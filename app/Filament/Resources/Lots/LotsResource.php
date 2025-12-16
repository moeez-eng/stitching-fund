<?php

namespace App\Filament\Resources\Lots;

use App\Filament\Resources\Lots\Pages\CreateLots;
use App\Filament\Resources\Lots\Pages\EditLots;
use App\Filament\Resources\Lots\Pages\ListLots;
use App\Filament\Resources\Lots\Schemas\LotsForm;
use App\Filament\Resources\Lots\Tables\LotsTable;
use App\Models\Lots;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LotsResource extends Resource
{
    protected static ?string $model = Lots::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'lots';

    public static function form(Schema $schema): Schema
    {
        return LotsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LotsTable::configure($table);
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
            'index' => ListLots::route('/'),
            'create' => CreateLots::route('/create'),
            'edit' => EditLots::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Check if user has the required role
        $allowedRoles = ['Super Admin', 'Agency Owner'];
        $hasAccess = in_array($user->role, $allowedRoles);
        
        Log::info('Has access: ' . ($hasAccess ? 'true' : 'false'));
        
        return $hasAccess;
    }
}
