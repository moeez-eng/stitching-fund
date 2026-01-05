<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
            if (empty($investmentPool->status)) {
                $investmentPool->status = self::STATUS_OPEN;
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
            
            // Ensure design_name is set from lat_id if not provided
            if (empty($investmentPool->design_name) && $investmentPool->lat_id) {
                $investmentPool->design_name = \App\Models\Lat::find($investmentPool->lat_id)?->design_name;
            }

            // Check if all investors have wallets
            if (isset($investmentPool->partners) && is_array($investmentPool->partners)) {
                foreach ($investmentPool->partners as $partner) {
                    if (isset($partner['investor_id'])) {
                        $investor = \App\Models\User::find($partner['investor_id']);
                        if (!$investor) {
                            throw new \Exception("Investor with ID {$partner['investor_id']} not found.");
                        }
                        $wallet = \App\Models\Wallet::where('investor_id', $investor->id)->first();
                        if (!$wallet) {
                            throw new \Exception("Investor '{$investor->name}' (ID: {$investor->id}) does not have a wallet. Please create a wallet for this investor first.");
                        }
                    }
                }
            }
        });
        
        static::updating(function ($investmentPool) {
            // Recalculate totals when partners are updated
            if (isset($investmentPool->partners) && is_array($investmentPool->partners)) {
                $totalCollected = 0;
                foreach ($investmentPool->partners as $partner) {
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
