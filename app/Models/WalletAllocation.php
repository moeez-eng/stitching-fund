<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'investor_id',
        'investment_pool_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected static function booted()
{
    static::saved(function ($allocation) {
        // Update the wallet's available balance
        $wallet = $allocation->wallet;
        if ($wallet) {
            $wallet->touch(); // This will update the updated_at timestamp
        }
    });
    
    static::deleted(function ($allocation) {
        // Update the wallet's available balance when an allocation is deleted
        $wallet = $allocation->wallet;
        if ($wallet) {
            $wallet->touch(); // This will update the updated_at timestamp
        }
    });
}

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function investmentPool()
    {
        return $this->belongsTo(InvestmentPool::class, 'investment_pool_id');
    }
}
