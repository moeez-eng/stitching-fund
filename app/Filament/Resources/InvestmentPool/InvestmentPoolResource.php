<?php

namespace App\Filament\Resources\InvestmentPool;

use UnitEnum;
use BackedEnum;
use App\Models\Lat;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use App\Models\InvestmentPool;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Log;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Placeholder;
use App\Filament\Resources\InvestmentPool\Pages\EditInvestmentPool;
use App\Filament\Resources\InvestmentPool\Pages\ViewInvestmentPool;
use App\Filament\Resources\InvestmentPool\Pages\ListInvestmentPools;
use App\Filament\Resources\InvestmentPool\Pages\CreateInvestmentPool;
use App\Filament\Resources\InvestmentPool\Schemas\InvestmentPoolForm;
use App\Filament\Resources\InvestmentPool\Tables\InvestmentPoolTable;

class InvestmentPoolResource extends Resource
{
    protected static ?string $model = InvestmentPool::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Investment Management';

    public static function form(Schema $schema): Schema
    {
        $schema = InvestmentPoolForm::configure($schema);
        
        // If we're creating from a LAT, pre-fill the lat_id
        if (request()->has('lat_id')) {
            $components = $schema->getComponents();
            $components[] = \Filament\Forms\Components\Hidden::make('lat_id')
                ->default(request('lat_id'));
            $schema->schema($components);
        }
        
        return $schema;
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
            'create' => CreateInvestmentPool::route('/create'),
            'view' => ViewInvestmentPool::route('/{record}'),
            'edit' => EditInvestmentPool::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Additional check for investors - must have a valid inviter
        if ($user->role === 'Investor') {
            $hasValidInviter = $user->invited_by && 
                             \App\Models\User::where('id', $user->invited_by)
                                            ->where('role', 'Agency Owner')
                                            ->exists();
            
            if (!$hasValidInviter) {
                Log::warning('Investor has no valid agency owner', [
                    'user_id' => $user->id,
                    'invited_by' => $user->invited_by
                ]);
                return false;
            }
        }
        
        return in_array($user->role, ['Super Admin', 'Agency Owner', 'Investor']);
    }


 public static function canView($record): bool
{
    $user = Auth::user();
    if (!$user) {
        Log::warning('No authenticated user');
        return false;
    }
    
    // Debug log
    Log::info('canView check', [
        'user_id' => $user->id,
        'user_role' => $user->role,
        'record_user_id' => $record->user_id,
        'user_invited_by' => $user->invited_by,
        'record_status' => $record->status
    ]);

    // Super Admin can view all pools regardless of status
    if ($user->role === 'Super Admin') {
        $canView = true;
        Log::info('Super Admin access - viewing pool', ['pool_id' => $record->id, 'status' => $record->status]);
        return $canView;
    }
    
    // Agency Owner can view their own pools regardless of status
    if ($user->role === 'Agency Owner' && $record->user_id === $user->id) {
        $canView = true;
        Log::info('Agency Owner access - viewing pool', ['user_id' => $user->id, 'pool_id' => $record->id, 'status' => $record->status]);
        return $canView;
    }
    
    // Investor can view pools from their inviter regardless of status
    if ($user->role === 'Investor' && $user->invited_by === $record->user_id) {
        $canView = true;
        Log::info('Investor access - viewing pool', [
            'investor_id' => $user->id,
            'pool_id' => $record->id,
            'status' => $record->status,
            'invited_by' => $user->invited_by,
            'pool_owner' => $record->user_id
        ]);
        return $canView;
    }
    
    Log::warning('Access denied', [
        'user_id' => $user->id,
        'role' => $user->role,
        'reason' => 'No matching access rule'
    ]);
    return false;
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
    
    // Super Admin and Agency Owner can create
    return in_array($user->role, ['Super Admin', 'Agency Owner']);
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

    protected static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        
        // If lat_id is passed in the request, use it
        if (request()->has('lat_id')) {
            $data['lat_id'] = request('lat_id');
            $lat = \App\Models\Lat::find($data['lat_id']);
            if ($lat) {
                $data['design_name'] = $lat->design_name;
                // Optionally set amount_required from LAT's materials and expenses
                $materialsTotal = $lat->materials->sum('price');
                $expensesTotal = $lat->expenses->sum('price');
                $data['amount_required'] = $materialsTotal + $expensesTotal;
            }
        }
        
        return $data;
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

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        // Hide from navigation if we're in a LAT context
        return $user && in_array($user->role, ['Super Admin', 'Agency Owner', 'Investor']) && !request()->has('lat_id');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        // Debug: Log current user info
        Log::info('Current user info', [
            'user_id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
            'invited_by' => $user->invited_by,
            'has_inviter' => (bool)$user->invited_by
        ]);

        $query = parent::getEloquentQuery();

        // If we're viewing from a LAT, only show pools for that LAT
        if (request()->has('lat_id')) {
            $query->where('lat_id', request('lat_id'));
        }

        // Super Admin sees all investment pools
        if ($user->role === 'Super Admin') {
            Log::info('Admin access - showing all pools');
            return $query;
        }

        // Agency Owner sees only their own investment pools
        if ($user->role === 'Agency Owner') {
            Log::info('Agency Owner access - showing pools for user_id: ' . $user->id);
            
            // Ensure the user can only see their own pools
            return $query->where('user_id', $user->id);
        }

        // Investor sees investment pools from their inviter (Agency Owner)
        if ($user->role === 'Investor') {
            $invitedBy = $user->invited_by;
            
            Log::info('Investor access check', [
                'investor_id' => $user->id,
                'invited_by' => $invitedBy,
                'has_inviter' => (bool)$invitedBy
            ]);
            
            if ($invitedBy) {
                // Verify the inviter is an Agency Owner
                $inviter = \App\Models\User::find($invitedBy);
                
                if ($inviter && $inviter->role === 'Agency Owner') {
                    // Debug the query
                    $poolCount = $query->where('user_id', $invitedBy)
                                     ->where('status', 'active') // Only show active pools
                                     ->count();
                    
                    Log::info('Investor pool access', [
                        'pools_found' => $poolCount,
                        'query' => $query->where('user_id', $invitedBy)->toSql(),
                        'bindings' => $query->getBindings()
                    ]);
                    
                    // Show active investment pools belonging to the investor's inviter
                    return $query->where('user_id', $invitedBy)
                               ->where('status', 'active');
                } else {
                    Log::warning('Investor has invalid inviter', [
                        'investor_id' => $user->id,
                        'inviter_id' => $invitedBy,
                        'inviter_role' => $inviter ? $inviter->role : 'not_found'
                    ]);
                }
            }
            
            // If no valid inviter, show nothing
            Log::warning('Investor has no valid inviter assigned', ['user_id' => $user->id]);
            return $query->whereNull('id');
        }

        // Default: show nothing for unauthorized roles
        Log::warning('Unauthorized access attempt', [
            'user_id' => $user->id,
            'role' => $user->role
        ]);
        return $query->whereNull('id');
    }
 protected function handleRecordUpdate(Model $record, array $data): Model
{
    DB::beginTransaction();
    try {
        // Update the investment pool
        $record->update($data);
        
        // Process wallet allocations if partners exist in the request
        if (isset($data['partners'])) {
            // First, delete any existing wallet allocations for this pool
            \App\Models\WalletAllocation::where('investment_pool_id', $record->id)->delete();
            
            foreach ($data['partners'] as $partner) {
                if (empty($partner['investor_id']) || empty($partner['investment_amount'])) {
                    continue;
                }
                
                // Find the investor's wallet
                $wallet = \App\Models\Wallet::where('investor_id', $partner['investor_id'])->first();
                
                if ($wallet) {
                    // Create the wallet allocation
                    \App\Models\WalletAllocation::create([
                        'wallet_id' => $wallet->id,
                        'investor_id' => $partner['investor_id'],
                        'investment_pool_id' => $record->id,
                        'amount' => $partner['investment_amount'],
                    ]);
                  
                }
            }
        }
        
        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
    
    return $record;
}
}
