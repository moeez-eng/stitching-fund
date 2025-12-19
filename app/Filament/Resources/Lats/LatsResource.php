<?php

namespace App\Filament\Resources\Lats;

use BackedEnum;
use App\Models\Lat;
use Filament\Tables\Table;
use App\Models\LatMaterial;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
// use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\Lats\Pages\EditLats;
use App\Filament\Resources\Lats\Pages\ListLats;
use App\Filament\Resources\Lats\Pages\CreateLats;
use App\Filament\Resources\Lats\Pages\LatDetails;
use App\Filament\Resources\Lats\Schemas\LatsForm;
use App\Filament\Resources\Lats\Tables\LatsTable;

class LatsResource extends Resource
{
    protected static ?string $model = Lat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'lats';

    public static function form(Schema $schema): Schema
    {
        return LatsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LatsTable::configure($table);
    }

   

   public static function getPages(): array
    {
        return [
            'index' => Pages\ListLats::route('/'),
            'create' => Pages\CreateLats::route('/create'),
            'view' => Pages\LatDetails::route('/{record}'),
            'edit' => Pages\EditLats::route('/{record}/edit'),
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

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }
     public static function getRelations(): array
    {
        return [
            RelationManagers\MaterialsRelationManager::class,
            RelationManagers\ExpenseRelationManager::class,
        ];
    }
   
}
