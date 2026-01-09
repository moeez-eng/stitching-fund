<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class InvestmentPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat_id',
        'design_name',
        'amount_required',
        'number_of_partners',
        'total_collected',
        'percentage_collected',
        'remaining_amount',
        'partners',
        'status',
        'user_id',
    ];

    protected $appends = [
        'is_fully_funded',
    ];

    protected $casts = [
        'amount_required' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'number_of_partners' => 'integer',
        'partners' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_OPEN = 'open';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_FULLY_FUNDED = 'fully_funded';
    public const STATUS_CLOSED = 'closed';

    protected static function booted()
    {
        static::creating(function ($investmentPool) {
            // Set default status
            if (empty($investmentPool->status)) {
                $investmentPool->status = 'open';
            }
        });

        static::updating(function ($investmentPool) {
            // Get the original model data before update
            $originalPartners = $investmentPool->getOriginal('partners');
            $newPartners = $investmentPool->partners;
            
            // Calculate total_collected from partners
            if (isset($newPartners) && is_array($newPartners)) {
                $totalCollected = 0;
                
                // Process each partner to update wallet allocations
                foreach ($newPartners as $index => $partner) {
                    if (!empty($partner['investor_id']) && isset($partner['investment_amount'])) {
                        $investorId = intval($partner['investor_id']);
                        $newAmount = floatval($partner['investment_amount']);
                        $originalAmount = 0;
                        
                        // Find the original amount if this partner existed before
                        if (is_array($originalPartners)) {
                            foreach ($originalPartners as $originalPartner) {
                                if (isset($originalPartner['investor_id']) && 
                                    intval($originalPartner['investor_id']) === $investorId) {
                                    $originalAmount = floatval($originalPartner['investment_amount'] ?? 0);
                                    break;
                                }
                            }
                        }
                        
                        // If amount changed, update the wallet allocation
                        if ($newAmount != $originalAmount) {
                            $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                            
                            if ($wallet) {
                                $allocation = \App\Models\WalletAllocation::where([
                                    'investor_id' => $investorId,
                                    'investment_pool_id' => $investmentPool->id
                                ])->first();
                                
                                if ($allocation) {
                                    // Calculate the difference to adjust the wallet balance
                                    $amountDifference = $originalAmount - $newAmount;
                                    
                                    // Update wallet allocation
                                    $allocation->amount = $newAmount;
                                    $allocation->save();
                                    
                                    // Create ledger entry for the adjustment
                                    if ($amountDifference != 0) {
                                        if ($amountDifference > 0) {
                                            // Return money to wallet
                                            \App\Models\WalletLedger::createReturn($wallet, abs($amountDifference), "Investment adjustment for pool #{$investmentPool->id}");
                                        } else {
                                            // Deduct additional money from wallet
                                            \App\Models\WalletLedger::createInvestment($wallet, abs($amountDifference), $allocation, "Additional investment for pool #{$investmentPool->id}");
                                        }
                                    }
                                    
                                    Log::info('Updated wallet allocation', [
                                        'investor_id' => $investorId,
                                        'old_amount' => $originalAmount,
                                        'new_amount' => $newAmount,
                                        'wallet_balance' => $wallet->available_balance
                                    ]);
                                }
                            }
                        }
                        
                        $totalCollected += $newAmount;
                    }
                }
                
                $investmentPool->total_collected = $totalCollected;
                
                // Calculate percentage_collected
                if ($investmentPool->amount_required > 0) {
                    $investmentPool->percentage_collected = min(100, round(($totalCollected / $investmentPool->amount_required) * 100, 0));
                } else {
                    $investmentPool->percentage_collected = 0;
                }
                
                // Calculate remaining_amount
                $investmentPool->remaining_amount = max(0, $investmentPool->amount_required - $totalCollected);
                
                // Update status based on remaining amount
                if ($investmentPool->remaining_amount > 0) {
                    $investmentPool->status = self::STATUS_OPEN; // Still needs money
                } else {
                    $investmentPool->status = self::STATUS_ACTIVE; // Fully funded, no need to add money
                }
            } else {
                $investmentPool->total_collected = 0;
                $investmentPool->percentage_collected = 0;
                $investmentPool->remaining_amount = $investmentPool->amount_required ?? 0;
            }
        });

        static::created(function ($investmentPool) {
            Log::info('InvestmentPool created event fired', [
                'pool_id' => $investmentPool->id,
                'partners' => $investmentPool->partners
            ]);
            
            // Calculate and ensure equal distribution of investment amounts
            if (isset($investmentPool->partners) && is_array($investmentPool->partners) && isset($investmentPool->amount_required)) {
                $amountRequired = floatval($investmentPool->amount_required);
                $numberOfPartners = intval($investmentPool->number_of_partners);
                
                if ($numberOfPartners > 0 && $amountRequired > 0) {
                    $perPartnerAmount = $amountRequired / $numberOfPartners;
                    
                    // Create a copy of partners array to modify
                    $partners = $investmentPool->partners;
                    
                    // Update each partner's investment amount to ensure equal distribution
                    foreach ($partners as $index => $partner) {
                        if (!empty($partner['investor_id'])) {
                            $partners[$index]['investment_amount'] = round($perPartnerAmount, 0);
                            $partners[$index]['investment_percentage'] = round(($perPartnerAmount / $amountRequired) * 100, 2);
                        }
                    }
                    
                    // Update partners property with modified array and save
                    $investmentPool->partners = $partners;
                    $investmentPool->saveQuietly(); // Save without firing events again
                    
                    // Calculate totals
                    $totalCollected = 0;
                    foreach ($partners as $partner) {
                        if (isset($partner['investment_amount'])) {
                            $totalCollected += floatval($partner['investment_amount']);
                        }
                    }
                    
                    // Update pool totals
                    $investmentPool->total_collected = $totalCollected;
                    $investmentPool->percentage_collected = (int) min(100, round(($totalCollected / $amountRequired) * 100));
                    $investmentPool->remaining_amount = max(0, $amountRequired - $totalCollected);
                    
                    // Update status based on remaining amount
                    if ($investmentPool->remaining_amount > 0) {
                        $investmentPool->status = self::STATUS_OPEN;
                    } else {
                        $investmentPool->status = self::STATUS_ACTIVE;
                    }
                    
                    $investmentPool->saveQuietly();
                }
            }
            
            // Process wallet allocations for partners
            if (isset($investmentPool->partners) && is_array($investmentPool->partners)) {
                Log::info('All partners data', [
                    'pool_id' => $investmentPool->id,
                    'partners' => $investmentPool->partners
                ]);
                
                $partnersData = array_filter($investmentPool->partners, function($partner) {
                    $hasInvestorId = !empty($partner['investor_id']);
                    $hasAmount = !empty($partner['investment_amount']);
                    
                    Log::info('Checking partner', [
                        'investor_id' => $partner['investor_id'] ?? 'null',
                        'investment_amount' => $partner['investment_amount'] ?? 'null',
                        'has_investor_id' => $hasInvestorId,
                        'has_amount' => $hasAmount
                    ]);
                    
                    return $hasInvestorId && $hasAmount;
                });
                
                Log::info('Processing wallet allocations', [
                    'pool_id' => $investmentPool->id,
                    'total_partners' => count($investmentPool->partners),
                    'valid_partners' => count($partnersData)
                ]);
                
                foreach ($partnersData as $partner) {
                    $investorId = intval($partner['investor_id']);
                    $investmentAmount = floatval($partner['investment_amount']);
                    
                    Log::info('Processing partner', [
                        'investor_id' => $investorId,
                        'amount' => $investmentAmount
                    ]);
                    
                    if (!$investorId || $investmentAmount <= 0) {
                        Log::error('Invalid partner data', [
                            'investor_id' => $investorId,
                            'amount' => $investmentAmount
                        ]);
                        continue;
                    }
                    
                    // Find investor's wallet
                    $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                    
                    if ($wallet) {
                        Log::info('Wallet found', [
                            'wallet_id' => $wallet->id,
                            'balance' => $wallet->amount
                        ]);
                        
                        // Check if wallet has sufficient balance
                        if ($wallet->available_balance < $investmentAmount) {
                            Log::error('Insufficient funds', [
                                'investor_id' => $investorId,
                                'available' => $wallet->available_balance,
                                'required' => $investmentAmount
                            ]);
                            continue;
                        }
                        
                        try {
                            // Create wallet allocation first
                            $allocation = \App\Models\WalletAllocation::create([
                                'wallet_id' => $wallet->id,
                                'investor_id' => $investorId,
                                'investment_pool_id' => $investmentPool->id,
                                'amount' => $investmentAmount,
                            ]);
                            
                            // Then create ledger entry using the allocation
                            \App\Models\WalletLedger::createInvestment($wallet, $allocation->amount, $allocation, "Investment in pool #{$investmentPool->id}");
                            
                            if ($allocation) {
                                Log::info('Wallet allocation created successfully', [
                                    'allocation_id' => $allocation->id,
                                    'investor_id' => $investorId,
                                    'amount' => $investmentAmount,
                                    'pool_id' => $investmentPool->id
                                ]);
                            } else {
                                Log::error('Failed to create allocation', ['investor_id' => $investorId]);
                            }
                        } catch (\Exception $e) {
                            Log::error('Exception during wallet allocation', [
                                'investor_id' => $investorId,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                        }
                    } else {
                        Log::error('Wallet not found for investor', ['investor_id' => $investorId]);
                    }
                }
            }
        });
    }
    
    public function walletAllocations()
    {
        return $this->hasMany(\App\Models\WalletAllocation::class, 'investment_pool_id');
    }
    
    

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function lat()
    {
        return $this->belongsTo(\App\Models\Lat::class);
    }
    
    public function getRemainingAmountAttribute()
    {
        return max(0, $this->amount_required - $this->total_collected);
    }
    
    public function getIsFullyFundedAttribute()
    {
        return $this->collected_amount >= $this->required_amount;
    }
    
    public function getPercentageFundedAttribute()
    {
        if ($this->amount_required <= 0) {
            return 0;
        }
        return min(100, round(($this->total_collected / $this->amount_required) * 100, 2));
    }

        public function investors()
    {
        return $this->belongsToMany(User::class, 'investment_pool_user')
            ->withPivot('investment_amount', 'investment_percentage')
            ->withTimestamps();
    }
}
