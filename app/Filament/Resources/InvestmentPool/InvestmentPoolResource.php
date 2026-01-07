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
            
          
        }
        
        return in_array($user->role, ['Super Admin', 'Agency Owner', 'Investor']);
    }


 public static function canView($record): bool
{
    $user = Auth::user();
    if (!$user) {
        // No logging - removed UTF-8 corruption
        return false;
    }
    
   
    // Super Admin can view all pools regardless of status
    if ($user->role === 'Super Admin') {
        $canView = true;
        // No logging - removed UTF-8 corruption
        return $canView;
    }
    
    // Agency Owner can view their own pools regardless of status
    if ($user->role === 'Agency Owner' && $record->user_id === $user->id) {
        $canView = true;
        // No logging - removed UTF-8 corruption
        return $canView;
    }
    
    // Investor can view pools from their inviter regardless of status
    if ($user->role === 'Investor' && $user->invited_by === $record->user_id) {
        $canView = true;
        // No logging - removed UTF-8 corruption
        return $canView;
    }
    
    // No logging - removed UTF-8 corruption
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

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        // Hide from navigation if we're in a LAT context
        return $user && in_array($user->role, ['Super Admin', 'Agency Owner', 'Investor']) && !request()->has('lat_id');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        // If we're viewing from a LAT, only show pools for that LAT
        if (request()->has('lat_id')) {
            $query->where('lat_id', request('lat_id'));
        }

        // Super Admin sees all investment pools
        if ($user->role === 'Super Admin') {
        return $query;
    }

        // Agency Owner sees only their own investment pools
        if ($user->role === 'Agency Owner') {
        return $query->where('user_id', $user->id);
    }

        // Investor sees investment pools from their inviter (Agency Owner)
        if ($user->role === 'Investor') {
            $invitedBy = $user->invited_by;
            
            if ($invitedBy) {
                // Verify the inviter is an Agency Owner
                $inviter = \App\Models\User::find($invitedBy);
                
                if ($inviter && $inviter->role === 'Agency Owner') {
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
            
            Log::warning('Investor has no valid inviter assigned', ['user_id' => $user->id]);
            return $query->whereNull('id');
        }

        // Default: show nothing for unauthorized roles
    return $query->whereNull('id');
    }

    protected static function handleRecordCreation(array $data): Model
    {
        Log::info('handleRecordCreation called', ['data_keys' => array_keys($data)]);
        
        // Test notification at the very beginning
        \Filament\Notifications\Notification::make()
            ->title('Pool Creation Started')
            ->body('Creating investment pool...')
            ->info()
            ->send();
        
        // Check if partners data is in individual fields format
        $partnersData = [];
        if (isset($data['partners']) && is_array($data['partners'])) {
            // Filter out empty partner entries and validate data
            $partnersData = array_filter($data['partners'], function($partner) {
                return !empty($partner['investor_id']) && !empty($partner['investment_amount']);
            });
        } elseif (isset($data['partners']) && is_string($data['partners'])) {
            // Parse JSON string
            $decodedData = json_decode($data['partners'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decodedData)) {
                // Filter out empty entries
                $partnersData = array_filter($decodedData, function($partner) {
                    return !empty($partner['investor_id']) && !empty($partner['investment_amount']);
                });
            } else {
                Log::error('Failed to decode partners JSON', ['json_error' => json_last_error_msg(), 'data' => $data['partners']]);
                $partnersData = [];
            }
        } else {
            // Try to build partners array from individual fields
            $partnersData = [];
            for ($i = 0; $i < 6; $i++) {
                $investorId = $data["partners.{$i}.investor_id"] ?? null;
                $investmentAmount = $data["partners.{$i}.investment_amount"] ?? null;
                $investmentPercentage = $data["partners.{$i}.investment_percentage"] ?? null;
                
                if ($investorId && $investmentAmount) {
                    $partnersData[] = [
                        'investor_id' => $investorId,
                        'investment_amount' => $investmentAmount,
                        'investment_percentage' => $investmentPercentage,
                    ];
                }
            }
        }
        
        Log::info('Processed partners data', ['count' => count($partnersData), 'data' => $partnersData]);
        
     
   
        DB::beginTransaction();
        try {
            // Create the investment pool first (equal distribution is handled in the model)
    
            $record = static::getModel()::create($data);
            
            // Debug: Check if partners data exists
            session()->flash('debug', 'Partners data: ' . (empty($partnersData) ? 'EMPTY' : 'FOUND - ' . count($partnersData) . ' items'));
            
            // Store partners data in JSON column
            if (!empty($partnersData)) {
                Log::info('About to call processWalletAllocations', [
                    'pool_id' => $record->id,
                    'partners_count' => count($partnersData)
                ]);
                
                $record->partners = $partnersData;
                $record->save();
                
                // Process wallet allocations and get results
                $allocationResults = self::processWalletAllocations($record, $partnersData);
                
                Log::info('processWalletAllocations completed', [
                    'success_count' => count($allocationResults['success']),
                    'error_count' => count($allocationResults['errors']),
                    'results' => $allocationResults
                ]);
                
                // Show wallet deduction notification
                if (!empty($allocationResults['success'])) {
                    $successMessage = 'Wallet deductions successful: ' . implode(', ', $allocationResults['success']);
                    session()->flash('success', $successMessage);
                }
                
                if (!empty($allocationResults['errors'])) {
                    $errorMessage = 'Wallet deduction errors: ' . implode(', ', $allocationResults['errors']);
                    session()->flash('error', $errorMessage);
                }
            } else {
                // Test notification for no partners
                session()->flash('warning', 'No partners data was provided');
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
        
        return $record;
    }
    
    /**
     * Process wallet allocations for investment pool partners
     */
    private static function processWalletAllocations($record, $partnersData)
    {
        Log::info('processWalletAllocations called', [
            'pool_id' => $record->id,
            'partners_count' => count($partnersData),
            'partners_data' => $partnersData
        ]);
        
        $results = [
            'success' => [],
            'errors' => []
        ];
        
        foreach ($partnersData as $index => $partner) {
            // Debug: Check partner data
            $partnerInfo = "Partner " . ($index + 1) . ": ID=" . ($partner['investor_id'] ?? 'NULL') . ", Amount=" . ($partner['investment_amount'] ?? 'NULL');
            Log::info('Processing partner', ['partner_info' => $partnerInfo]);
            
            // Convert investor_id to integer and validate
            $investorId = isset($partner['investor_id']) ? intval($partner['investor_id']) : null;
            $investmentAmount = isset($partner['investment_amount']) ? floatval($partner['investment_amount']) : 0;
            
            if (!$investorId || $investmentAmount <= 0) {
                $errorMsg = "Partner " . ($index + 1) . ": Missing or invalid investor ID ({$investorId}) or investment amount ({$investmentAmount})";
                $results['errors'][] = $errorMsg;
                Log::error('Partner validation failed', ['error' => $errorMsg, 'partner' => $partner]);
                continue;
            }
            
            // Find investor's wallet
            $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
            
            if ($wallet) {
                // Debug: Wallet found
                $walletInfo = "Wallet found: ID=" . $wallet->id . ", Balance=" . $wallet->amount;
                Log::info('Wallet found', ['wallet_info' => $walletInfo]);
                
                // Check if wallet has sufficient balance
                if ($wallet->amount < $investmentAmount) {
                    $results['errors'][] = "Investor {$investorId}: Insufficient funds (Available: PKR {$wallet->amount}, Required: PKR {$investmentAmount})";
                    continue;
                }
                
                try {
                    // Deduct from wallet
                    $wallet->amount -= $investmentAmount;
                    $wallet->save();
                    
                    // Create wallet allocation
                    $allocation = \App\Models\WalletAllocation::create([
                        'wallet_id' => $wallet->id,
                        'investor_id' => $investorId,
                        'investment_pool_id' => $record->id,
                        'amount' => $investmentAmount,
                    ]);
                    
                    if ($allocation) {
                        $results['success'][] = "Investor {$investorId}: PKR {$investmentAmount} deducted and allocated successfully";
                        Log::info('Wallet allocation created', [
                            'allocation_id' => $allocation->id,
                            'investor_id' => $investorId,
                            'amount' => $investmentAmount,
                            'pool_id' => $record->id
                        ]);
                    } else {
                        $results['errors'][] = "Investor {$investorId}: Failed to create allocation";
                        Log::error('Failed to create allocation', ['investor_id' => $investorId]);
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = "Investor {$investorId}: " . $e->getMessage();
                    Log::error('Exception during wallet allocation', [
                        'investor_id' => $investorId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                $results['errors'][] = "Investor {$investorId}: Wallet not found";
                Log::error('Wallet not found for investor', ['investor_id' => $investorId]);
            }
        }
        
        return $results;
    }
}