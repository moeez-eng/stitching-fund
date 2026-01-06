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
                        $investmentPool->percentage_collected = min(100, round(($totalCollected / $investmentPool->amount_required) * 100, 2));
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
            // Get existing allocations for this pool
            $existingAllocations = \App\Models\WalletAllocation::where('investment_pool_id', $investmentPool->id)
                ->pluck('amount', 'investor_id')
                ->toArray();
            
            // Process wallet allocations if partners exist
            if (isset($investmentPool->partners) && is_array($investmentPool->partners)) {
                foreach ($investmentPool->partners as $partner) {
                    if (empty($partner['investor_id']) || empty($partner['investment_amount'])) {
                        continue;
                    }
                    
                    // Find the investor's wallet
                    $wallet = \App\Models\Wallet::where('investor_id', $partner['investor_id'])->first();
                    
                    if ($wallet) {
                        $newAmount = floatval($partner['investment_amount']);
                        $existingAmount = floatval($existingAllocations[$partner['investor_id']] ?? 0);
                        $amountDifference = $newAmount - $existingAmount;
                        
                        if ($amountDifference > 0) {
                            // Additional amount to deduct
                            if ($wallet->amount < $amountDifference) {
                                throw new \Exception("Insufficient funds in wallet for investor. Available: PKR {$wallet->amount}, Required: PKR {$amountDifference}");
                            }
                            $wallet->decrement('amount', $amountDifference);
                        } elseif ($amountDifference < 0) {
                            // Refund the difference
                            $wallet->increment('amount', abs($amountDifference));
                        }
                        
                        // Update or create the wallet allocation
                        \App\Models\WalletAllocation::updateOrCreate(
                            [
                                'wallet_id' => $wallet->id,
                                'investor_id' => $partner['investor_id'],
                                'investment_pool_id' => $investmentPool->id,
                            ],
                            [
                                'amount' => $newAmount,
                            ]
                        );
                    }
                }
                
                // Refund any investors who were removed
                foreach ($existingAllocations as $investorId => $amount) {
                    $stillExists = false;
                    foreach ($investmentPool->partners as $partner) {
                        if (($partner['investor_id'] ?? null) == $investorId) {
                            $stillExists = true;
                            break;
                        }
                    }
                    
                    if (!$stillExists && $amount > 0) {
                        $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                        if ($wallet) {
                            $wallet->increment('amount', $amount);
                        }
                        // Remove the allocation
                        \App\Models\WalletAllocation::where('investment_pool_id', $investmentPool->id)
                            ->where('investor_id', $investorId)
                            ->delete();
                    }
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
                        $investmentPool->percentage_collected = min(100, round(($totalCollected / $investmentPool->amount_required) * 100, 2));
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
            // Create wallet allocations when pool is created
            if (isset($investmentPool->partners) && is_array($investmentPool->partners)) {
                foreach ($investmentPool->partners as $partner) {
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
                            'investment_pool_id' => $investmentPool->id,
                            'amount' => $partner['investment_amount'],
                        ]);
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
        return max(0, $this->required_amount - $this->collected_amount);
    }
    
    public function getIsFullyFundedAttribute()
    {
        return $this->collected_amount >= $this->required_amount;
    }
    
    public function getPercentageFundedAttribute()
    {
        if ($this->required_amount <= 0) {
            return 0;
        }
        return min(100, round(($this->collected_amount / $this->required_amount) * 100, 2));
    }

        public function investors()
    {
        return $this->belongsToMany(User::class, 'investment_pool_user')
            ->withPivot('investment_amount', 'investment_percentage')
            ->withTimestamps();
    }
}
