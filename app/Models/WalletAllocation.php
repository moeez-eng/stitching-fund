<?php

namespace App\Models;

use App\Models\Wallet;
use App\Models\WalletLedger;
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
    static::created(function ($allocation) {
        // Create ledger entry for investment
        $wallet = $allocation->wallet;
        if ($wallet) {
            WalletLedger::createInvestment($wallet, $allocation->amount, $allocation);
        }
    });
    
    static::deleted(function ($allocation) {
        // Create ledger entry for investment return/cancellation
        $wallet = $allocation->wallet;
        if ($wallet) {
            WalletLedger::createReturn($wallet, $allocation->amount, "Investment cancelled/returned");
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
