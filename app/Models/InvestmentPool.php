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
        'remaining_amount',
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
    public const STATUS_FULLY_FUNDED = 'fully_funded';
    public const STATUS_CLOSED = 'closed';

    protected static function booted()
    {
        static::creating(function ($investmentPool) {
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
                            $partners[$index]['investment_amount'] = number_format($perPartnerAmount, 0);
                            $partners[$index]['investment_percentage'] = round(($perPartnerAmount / $amountRequired) * 100, 2);
                        }
                    }
                    
                    // Update partners property with modified array and ensure it's properly set
                    $investmentPool->partners = $partners;
                    $investmentPool->setAttribute('partners', $partners);
                }
            }
            
            // Calculate total_collected from partners
            if (isset($investmentPool->partners)) {
                $partners = $investmentPool->partners;
                    
                if (is_array($partners)) {
                    $totalCollected = 0;
                    foreach ($partners as $partner) {
                        if (isset($partner['investment_amount'])) {
                            $totalCollected += floatval($partner['investment_amount']);
                        }
                    }
                    $investmentPool->total_collected = $totalCollected;
                    
                    // Calculate percentage_collected
                    if ($investmentPool->amount_required > 0) {
                        $investmentPool->percentage_collected = (int) min(100, round(($totalCollected / $investmentPool->amount_required) * 100));
                    } else {
                        $investmentPool->percentage_collected = 0;
                    }
                    
                    // Calculate remaining_amount
                    $investmentPool->remaining_amount = max(0, $investmentPool->amount_required - $totalCollected);
                }
            } else {
                $investmentPool->total_collected = 0;
                $investmentPool->percentage_collected = 0;
                $investmentPool->remaining_amount = $investmentPool->amount_required ?? 0;
            }
            
            // Set default status
            if (empty($investmentPool->status)) {
                $investmentPool->status = 'open';
            }
        });

        static::updating(function ($investmentPool) {
            // Wallet allocations are now handled in the InvestmentPoolResource
            // to prevent double deduction during updates
            
            // Calculate total_collected from partners
            if (isset($investmentPool->partners)) {
                $partners = $investmentPool->partners;
                    
                if (is_array($partners)) {
                    $totalCollected = 0;
                    foreach ($partners as $partner) {
                        if (isset($partner['investment_amount'])) {
                            $totalCollected += floatval($partner['investment_amount']);
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
            
            // Process wallet allocations for partners
            if (isset($investmentPool->partners) && is_array($investmentPool->partners)) {
                $partnersData = array_filter($investmentPool->partners, function($partner) {
                    return !empty($partner['investor_id']) && !empty($partner['investment_amount']);
                });
                
                Log::info('Processing wallet allocations', [
                    'pool_id' => $investmentPool->id,
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
                        if ($wallet->amount < $investmentAmount) {
                            Log::error('Insufficient funds', [
                                'investor_id' => $investorId,
                                'available' => $wallet->amount,
                                'required' => $investmentAmount
                            ]);
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
                                'investment_pool_id' => $investmentPool->id,
                                'amount' => $investmentAmount,
                            ]);
                            
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
