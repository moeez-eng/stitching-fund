<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'investor_id',
        'investment_pool_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function investmentPool()
    {
        return $this->belongsTo(InvestmentPool::class, 'investment_pool_id');
    }
}
