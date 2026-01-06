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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
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
        
        // Handle pre-filled data from wallet
        if (request()->has('investor_id')) {
            $data['partners'] = [[
                'investor_id' => request('investor_id'),
                'investment_amount' => 0,
                'investment_percentage' => 0,
            ]];
        }
        
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
        Log::info('=== MUTATE FORM DATA BEFORE SAVE ===');
        Log::info('Data: ' . json_encode($data));
        
        // Handle wallet allocations if this is an update and partners data exists
        if (isset($data['partners']) && is_array($data['partners'])) {
            Log::info('Processing partners data in mutateFormDataBeforeSave');
            
            // Get the current record being updated
            $record = request()->route('record');
            if ($record) {
                Log::info('Updating existing record: ' . $record->id);
                
                // Get existing allocations for this pool
                $existingAllocations = \App\Models\WalletAllocation::where('investment_pool_id', $record->id)
                    ->pluck('amount', 'investor_id')
                    ->toArray();
                
                Log::info('Existing allocations: ' . json_encode($existingAllocations));
                
                // Process wallet changes
                foreach ($data['partners'] as $partner) {
                    if (empty($partner['investor_id']) || empty($partner['investment_amount'])) {
                        continue;
                    }
                    
                    // Find the investor's wallet
                    $wallet = \App\Models\Wallet::where('investor_id', $partner['investor_id'])->first();
                    
                    if ($wallet) {
                        $newAmount = floatval($partner['investment_amount']);
                        $existingAmount = floatval($existingAllocations[$partner['investor_id']] ?? 0);
                        $amountDifference = $newAmount - $existingAmount;
                        
                        Log::info("Processing wallet for investor {$partner['investor_id']}", [
                            'wallet_id' => $wallet->id,
                            'current_wallet_amount' => $wallet->amount,
                            'new_investment_amount' => $newAmount,
                            'existing_allocation' => $existingAmount,
                            'difference' => $amountDifference
                        ]);
                        
                        if ($amountDifference > 0) {
                            // Additional amount to deduct
                            if ($wallet->amount < $amountDifference) {
                                throw new \Exception("Insufficient funds in wallet for investor. Available: PKR {$wallet->amount}, Required: PKR {$amountDifference}");
                            }
                            $wallet->decrement('amount', $amountDifference);
                            Log::info("DEDUCTED: {$amountDifference} from wallet {$wallet->id}");
                        } elseif ($amountDifference < 0) {
                            // Refund the difference
                            $refundAmount = abs($amountDifference);
                            $wallet->increment('amount', $refundAmount);
                            Log::info("REFUNDED: {$refundAmount} to wallet {$wallet->id}");
                        } else {
                            Log::info("NO CHANGE: Investment amount stays the same");
                        }
                        
                        // Update or create the wallet allocation
                        \App\Models\WalletAllocation::updateOrCreate(
                            [
                                'wallet_id' => $wallet->id,
                                'investor_id' => $partner['investor_id'],
                                'investment_pool_id' => $record->id,
                            ],
                            [
                                'amount' => $newAmount,
                            ]
                        );
                        
                        Log::info("Updated allocation for investor {$partner['investor_id']} to {$newAmount}");
                    }
                }
                
                // Check for removed investors and refund them
                foreach ($existingAllocations as $investorId => $amount) {
                    $stillExists = false;
                    foreach ($data['partners'] as $partner) {
                        if (($partner['investor_id'] ?? null) == $investorId) {
                            $stillExists = true;
                            break;
                        }
                    }
                    
                    if (!$stillExists && $amount > 0) {
                        Log::info("Investor {$investorId} was removed, refunding {$amount}");
                        $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                        if ($wallet) {
                            $wallet->increment('amount', $amount);
                            Log::info("Refunded {$amount} to wallet {$wallet->id}");
                        }
                        // Remove the allocation
                        \App\Models\WalletAllocation::where('investment_pool_id', $record->id)
                            ->where('investor_id', $investorId)
                            ->delete();
                        Log::info("Deleted allocation for removed investor {$investorId}");
                    }
                }
            }
        }
        
        // Ensure design_name is set from lat_id if not provided
        if (isset($data['lat_id']) && empty($data['design_name'])) {
            $designName = Lat::find($data['lat_id'])?->design_name;
            $data['design_name'] = $designName;
        }

        // Process partners data to include investment_percentage and calculate totals
        if (isset($data['partners']) && is_array($data['partners'])) {
            $amountRequired = $data['amount_required'] ?? 0;
            $totalCollected = 0;
            
            $data['partners'] = collect($data['partners'])->map(function ($partner) use ($amountRequired, &$totalCollected) {
                if (isset($partner['investment_amount']) && $amountRequired > 0) {
                    $partner['investment_percentage'] = round(($partner['investment_amount'] / $amountRequired) * 100, 2);
                    $totalCollected += floatval($partner['investment_amount']);
                } else {
                    $partner['investment_percentage'] = 0;
                }
                return $partner;
            })->toArray();
            
            // Set calculated totals
            $data['total_collected'] = $totalCollected;
            $data['percentage_collected'] = $amountRequired > 0 ? min(100, round(($totalCollected / $amountRequired) * 100, 2)) : 0;
            $data['remaining_amount'] = max(0, $amountRequired - $totalCollected);
            
            Log::info('Processed partners data: ' . json_encode($data['partners']));
        } else {
            Log::info('No partners data found in mutateFormDataBeforeSave');
        }

        Log::info('Final data before save: ' . json_encode($data));
        Log::info('=== END MUTATE FORM DATA BEFORE SAVE ===');

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
                                     ->whereIn('status', ['open', 'active']) // Show open and active pools
                                     ->count();
                    
                    Log::info('Investor pool access', [
                        'pools_found' => $poolCount,
                        'query' => $query->where('user_id', $invitedBy)->toSql(),
                        'bindings' => $query->getBindings()
                    ]);
                    
                    // Show open and active investment pools belonging to the investor's inviter
                    return $query->where('user_id', $invitedBy)
                               ->whereIn('status', ['open', 'active']);
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
 protected function handleRecordCreation(array $data): Model
    {
        DB::beginTransaction();
        try {
            // Create the investment pool first
            $record = static::getModel()::create($data);
            
            // Process wallet allocations if partners exist
            if (isset($data['partners']) && is_array($data['partners'])) {
                foreach ($data['partners'] as $partner) {
                    if (empty($partner['investor_id']) || empty($partner['investment_amount'])) {
                        continue;
                    }
                    
                    // Find the investor's wallet
                    $wallet = \App\Models\Wallet::where('investor_id', $partner['investor_id'])->first();
                    
                    if ($wallet) {
                        // Check if wallet has sufficient balance
                        if ($wallet->amount < $partner['investment_amount']) {
                            throw new \Exception("Insufficient funds in wallet for investor '{$partner['investor_id']}'. Available: PKR {$wallet->amount}, Required: PKR {$partner['investment_amount']}");
                        }
                        
                        // Deduct from wallet
                        $wallet->decrement('amount', $partner['investment_amount']);
                        
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

    protected function handleRecordUpdate(Model $record, array $data): Model
{         
    Log::info('=== STARTING INVESTMENT POOL UPDATE ===');
    Log::info('Pool ID: ' . $record->id);
    Log::info('Data being updated: ' . json_encode($data));
    
    DB::beginTransaction();
    try {
        // Always process wallet allocations if partners data exists
        if (isset($data['partners']) && is_array($data['partners'])) {
            Log::info('Partners data found, processing wallet allocations');
            
            // Get existing allocations for this pool
            $existingAllocations = \App\Models\WalletAllocation::where('investment_pool_id', $record->id)
                ->pluck('amount', 'investor_id')
                ->toArray();
            
            Log::info('Existing allocations: ' . json_encode($existingAllocations));
            
            foreach ($data['partners'] as $partner) {
                if (empty($partner['investor_id']) || empty($partner['investment_amount'])) {
                    Log::info('Skipping partner - missing investor_id or investment_amount');
                    continue;
                }
                
                Log::info('Processing partner: ' . json_encode($partner));
                
                // Find the investor's wallet
                $wallet = \App\Models\Wallet::where('investor_id', $partner['investor_id'])->first();
                
                if ($wallet) {
                    $newAmount = floatval($partner['investment_amount']);
                    $existingAmount = floatval($existingAllocations[$partner['investor_id']] ?? 0);
                    $amountDifference = $newAmount - $existingAmount;
                    
                    Log::info("Wallet found for investor {$partner['investor_id']}", [
                        'wallet_id' => $wallet->id,
                        'current_wallet_amount' => $wallet->amount,
                        'new_investment_amount' => $newAmount,
                        'existing_allocation' => $existingAmount,
                        'difference' => $amountDifference
                    ]);
                    
                    if ($amountDifference > 0) {
                        // Additional amount to deduct
                        if ($wallet->amount < $amountDifference) {
                            throw new \Exception("Insufficient funds in wallet for investor. Available: PKR {$wallet->amount}, Required: PKR {$amountDifference}");
                        }
                        $wallet->decrement('amount', $amountDifference);
                        Log::info("DEDUCTED: {$amountDifference} from wallet {$wallet->id}. New balance: " . $wallet->fresh()->amount);
                    } elseif ($amountDifference < 0) {
                        // Refund the difference
                        $refundAmount = abs($amountDifference);
                        $wallet->increment('amount', $refundAmount);
                        Log::info("REFUNDED: {$refundAmount} to wallet {$wallet->id}. New balance: " . $wallet->fresh()->amount);
                    } else {
                        Log::info("NO CHANGE: Investment amount stays the same");
                    }
                    
                    // Update or create the wallet allocation
                    \App\Models\WalletAllocation::updateOrCreate(
                        [
                            'wallet_id' => $wallet->id,
                            'investor_id' => $partner['investor_id'],
                            'investment_pool_id' => $record->id,
                        ],
                        [
                            'amount' => $newAmount,
                        ]
                    );
                    
                    Log::info("Updated allocation record for investor {$partner['investor_id']} to {$newAmount}");
                } else {
                    Log::warning("No wallet found for investor {$partner['investor_id']}");
                }
            }
            
            // Check for removed investors and refund them
            foreach ($existingAllocations as $investorId => $amount) {
                $stillExists = false;
                foreach ($data['partners'] as $partner) {
                    if (($partner['investor_id'] ?? null) == $investorId) {
                        $stillExists = true;
                        break;
                    }
                }
                
                if (!$stillExists && $amount > 0) {
                    Log::info("Investor {$investorId} was removed, refunding {$amount}");
                    $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                    if ($wallet) {
                        $wallet->increment('amount', $amount);
                        Log::info("Refunded {$amount} to wallet {$wallet->id}");
                    }
                    // Remove the allocation
                    \App\Models\WalletAllocation::where('investment_pool_id', $record->id)
                        ->where('investor_id', $investorId)
                        ->delete();
                    Log::info("Deleted allocation for removed investor {$investorId}");
                }
            }
        } else {
            Log::info('No partners data found in update');
        }
        
        // Update the investment pool
        Log::info('Updating investment pool record...');
        $record->update($data);
        Log::info('Investment pool updated successfully');
        
        DB::commit();
        Log::info('=== INVESTMENT POOL UPDATE COMPLETED ===');
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating investment pool: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        throw $e;
    }
    
    return $record;
}
}
