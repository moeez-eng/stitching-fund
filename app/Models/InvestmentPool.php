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
            
            if (empty($investmentPool->total_collected)) {
                $investmentPool->total_collected = 0;
            }
            
            // Ensure design_name is set from lat_id if not provided
            if (empty($investmentPool->design_name) && $investmentPool->lat_id) {
                $investmentPool->design_name = \App\Models\Lat::find($investmentPool->lat_id)?->design_name;
            }

            // Check if all investors have wallets
            if (isset($investmentPool->partners)) {
                $partners = is_string($investmentPool->partners) 
                    ? json_decode($investmentPool->partners, true) 
                    : $investmentPool->partners;
                    
                if (is_array($partners)) {
                    foreach ($partners as $partner) {
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
