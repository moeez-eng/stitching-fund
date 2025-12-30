<?php

namespace App\Filament\Resources\InvestmentPool;

use App\Filament\Resources\InvestmentPool\Tables\InvestmentPoolTable;
use Filament\Schemas\Components\Placeholder;
use App\Filament\Resources\InvestmentPool\Pages\ViewInvestmentPool;
use App\Filament\Resources\InvestmentPool\Pages\EditInvestmentPool;
use App\Filament\Resources\InvestmentPool\Pages\ListInvestmentPools;
use App\Filament\Resources\InvestmentPool\Pages\CreateInvestmentPool;
use App\Filament\Resources\InvestmentPool\Schemas\InvestmentPoolForm;
use BackedEnum;
use UnitEnum;
use App\Models\Lat;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\InvestmentPool;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;

class InvestmentPoolResource extends Resource
{
    protected static ?string $model = InvestmentPool::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Investment Management';

    public static function form(Schema $schema): Schema
    {
        return InvestmentPoolForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvestmentPoolTable::configure($table);
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
            'index' => ListInvestmentPools::route('/'),
            'view' => ViewInvestmentPool::route('/{record}'),
            'edit' => EditInvestmentPool::route('/{record}/edit'),
        ];
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
        
        // Agency Owner sees their own investment pools
        if ($user->role === 'Agency Owner') {
            return $query->where('user_id', $user->id);
        }
        
        // Investor sees investment pools from their referenced agency owner
        if ($user->role === 'Investor') {
            // Get the agency owner for this investor
            $agencyOwnerId = $user->agency_owner_id;
            
            if ($agencyOwnerId) {
                // Show investment pools belonging to the investor's agency owner
                return $query->where('user_id', $agencyOwnerId);
            }
            
            // If no agency owner assigned, show nothing
            return $query->whereRaw('1 = 0');
        }
        
        return $query->whereRaw('1 = 0');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Super Admin can edit all
        if ($user->role === 'Super Admin') return true;
        
        // Agency Owner can edit their own pools
        if ($user->role === 'Agency Owner' && $record->user_id === $user->id) return true;
        
        // Investors cannot edit
        return false;
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Only Super Admin can create
        return $user->role === 'Super Admin';
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        if (!$user) return false;
        
        // Super Admin can delete all
        if ($user->role === 'Super Admin') return true;
        
        // Agency Owner can delete their own pools
        if ($user->role === 'Agency Owner' && $record->user_id === $user->id) return true;
        
        // Investors cannot delete
        return false;
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // Debug logging
        Log::info('mutateFormDataBeforeSave called with data: ', $data);

        // Ensure design_name is set from lat_id if not provided
        if (isset($data['lat_id']) && empty($data['design_name'])) {
            $designName = Lat::find($data['lat_id'])?->design_name;
            $data['design_name'] = $designName;
            Log::info('Setting design_name to: ' . $designName);
        }

        // Process partners data to include investment_percentage
        if (isset($data['partners']) && is_array($data['partners'])) {
            $amountRequired = $data['amount_required'] ?? 0;
            Log::info('Processing partners with amount_required: ' . $amountRequired);
            
            $data['partners'] = collect($data['partners'])->map(function ($partner) use ($amountRequired) {
                if (isset($partner['investment_amount']) && $amountRequired > 0) {
                    $partner['investment_percentage'] = round(($partner['investment_amount'] / $amountRequired) * 100);
                    Log::info('Partner percentage calculated: ' . $partner['investment_percentage']);
                } else {
                    $partner['investment_percentage'] = 0;
                }
                return $partner;
            })->toArray();
        }

        Log::info('Final data before save: ', $data);
        return $data;
    }
}
