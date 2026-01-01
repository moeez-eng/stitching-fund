<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'agency_owner_id',
        'investor_id',
        'amount',
        'slip_type',
        'slip_path',
        'reference',
        'deposited_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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

    public function allocations()
    {
        return $this->hasMany(WalletAllocation::class, 'wallet_id');
    }

        public function getAvailableBalanceAttribute()
    {
        $totalDeposited = (float)($this->amount ?? 0);
        $totalAllocated = (float)($this->allocations()->sum('amount') ?? 0);
        return max(0, $totalDeposited - $totalAllocated);
    }
    public function getTotalInvestedAttribute()
    {
        return (float)($this->allocations()->sum('amount') ?? 0);
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
}
