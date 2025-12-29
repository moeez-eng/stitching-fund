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
}
