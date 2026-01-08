<?php

namespace App\Models;

use App\Models\WalletAllocation;
use App\Models\WalletLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_owner_id',
        'investor_id',
        'total_deposits', // Lifetime deposited amount (renamed from amount)
        'slip_type',
        'slip_path',
        'reference',
        'deposited_at',
    ];

    protected $casts = [
        'total_deposits' => 'decimal:2', // Lifetime deposited
        'deposited_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($wallet) {
            if (Auth::check() && !$wallet->agency_owner_id) {
                if(Auth::user()->role === 'Agency Owner'){
                    $wallet->agency_owner_id = Auth::id();
                } elseif (Auth::user()->role === 'Investor') {
                    $wallet->agency_owner_id = Auth::user()->agency_owner_id;
                }
            }
            
            // Handle deposit_amount field from form
            if (isset($wallet->deposit_amount)) {
                // For new wallet, set total_deposits to deposit_amount
                $wallet->total_deposits = $wallet->deposit_amount;
                unset($wallet->deposit_amount);
            }
        });
        
        static::created(function ($wallet) {
            // Create initial ledger entry for new wallet
            if ($wallet->total_deposits > 0) {
                WalletLedger::createDeposit($wallet, $wallet->total_deposits, "Initial deposit", $wallet->reference);
            }
        });
        
        static::updating(function ($wallet) {
            // Handle deposit_amount field for existing wallets
            if (isset($wallet->deposit_amount)) {
                $depositAmount = $wallet->deposit_amount;
                
                // Add to lifetime deposits
                $wallet->total_deposits += $depositAmount;
                
                // Create ledger entry
                WalletLedger::createDeposit($wallet, $depositAmount, "Additional deposit", $wallet->reference);
                
                unset($wallet->deposit_amount); // Remove virtual field
            }
            
            // Prevent total_deposits from decreasing
            if ($wallet->isDirty('total_deposits')) {
                $oldTotal = $wallet->getOriginal('total_deposits');
                $newTotal = $wallet->total_deposits;
                
                if ($newTotal < $oldTotal) {
                    // Prevent decreasing total_deposits
                    $wallet->total_deposits = $oldTotal;
                }
            }
        });
    }

    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function agencyOwner()
    {
        return $this->belongsTo(User::class, 'agency_owner_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(WalletAllocation::class);
    }

    public function ledgers(): HasMany
    {
        return $this->hasMany(WalletLedger::class);
    }
    public function getLifetimeDepositedAttribute(): float
    {
        return (float)($this->total_deposits ?? 0);
    }

    public function getActiveInvestedAttribute(): float
    {
        return (float)($this->allocations()->sum('amount') ?? 0);
    }

    public function getTotalReturnedAttribute(): float
    {
        // Calculate from ledger entries - sum of all return and profit transactions
        $returns = $this->ledgers()
            ->whereIn('type', ['return', 'profit'])
            ->sum('amount') ?? 0;
            
        return (float)$returns;
    }

    public function getAvailableBalanceAttribute(): float
    {
        // Use ledger calculation instead of direct calculation
        return WalletLedger::getAvailableBalance($this);
    }

    public function getWalletStatusAttribute()
    {
        $balance = floatval($this->available_balance ?? 0);
        
        if ($balance <= 0) {
            return ['status' => 'empty', 'color' => 'danger', 'text' => 'Empty Wallet'];
        } elseif ($balance < 50000) {
            return ['status' => 'low', 'color' => 'warning', 'text' => 'Low Balance'];
        } else {
            return ['status' => 'healthy', 'color' => 'success', 'text' => 'Healthy Balance'];
        }
    }
    public function canAllocate(float $amount): bool
    {
        return $this->available_balance >= $amount;
    }
    public function getAllocatedBalanceAttribute(): float
    {
        return (float)$this->allocations()
            ->whereIn('status', ['invested', 'pending'])
            ->sum('amount');
    }
    
    /**
     * Add deposit to wallet - only increases total_deposits
     */
    public function addDeposit(float $amount): bool
    {
        if ($amount <= 0) {
            return false;
        }
        
        $this->amount += $amount;
        $this->total_deposits += $amount;
        
        return $this->save();
    }
}
