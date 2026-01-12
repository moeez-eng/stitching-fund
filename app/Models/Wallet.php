<?php

namespace App\Models;

use App\Models\WalletAllocation;
use App\Models\WalletLedger;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_owner_id',
        'investor_id',
        'total_deposits', 
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
            
            // Handle total_deposits field from form
            if (isset($wallet->total_deposits)) {
                // total_deposits is already the correct field, no need to reassign
            }
        });
        
        static::created(function ($wallet) {
            // Create initial ledger entry for new wallet
            Log::info('Wallet created event fired', ['wallet_id' => $wallet->id, 'total_deposits' => $wallet->total_deposits]);
            
            if ($wallet->total_deposits > 0) {
                try {
                    WalletLedger::createDeposit($wallet, $wallet->total_deposits, "Initial deposit", $wallet->reference);
                    Log::info('Ledger entry created for wallet', ['wallet_id' => $wallet->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to create ledger entry', ['error' => $e->getMessage(), 'wallet_id' => $wallet->id]);
                }
            }
        });
        
        static::updating(function ($wallet) {
            // Handle total_deposits field for existing wallets
            if (isset($wallet->total_deposits)) {
                // total_deposits is already the correct field, no additional logic needed
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

    public function withdrawalRequests(): HasMany
    {
        return $this->hasMany(WithdrawalRequest::class);
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
    
    /**
     * Refresh the available balance (clear any cache)
     */
    public function refreshAvailableBalance(): float
    {
        // Clear any cached balance
        unset($this->attributes['available_balance']);
        
        // Recalculate from ledger
        return $this->available_balance;
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
