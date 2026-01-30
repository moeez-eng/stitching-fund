<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Lat extends Model
{


    protected static function booted()
    {
        static::creating(function ($lat) {
            if (Auth::check() && !$lat->user_id) {
                $lat->user_id = Auth::id();
            }
            
            // Set default values for financial fields if not provided
            $lat->market_payments_received = $lat->market_payments_received ?? 0;
            $lat->payment_status = $lat->payment_status ?? 'pending';
            $lat->total_with_profit = $lat->total_with_profit ?? 0;
            $lat->profit_percentage = $lat->profit_percentage ?? 10;
            $lat->pieces = $lat->pieces ?? 1;
        });
    }

    protected $fillable = [
        'lat_no',
        'design_name',
        'customer_name',
        'total_price',
        'pieces',
        'profit_percentage',
        'initial_investment',
        'company_name',
        'user_id',
        'market_payments_received',
        'payment_status',
        'total_with_profit',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'pieces' => 'integer',
        'profit_percentage' => 'decimal:2',
        'initial_investment' => 'decimal:2',
        'market_payments_received' => 'decimal:2',
        'total_with_profit' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class);
    }

    public function materials()
    {
        return $this->hasMany(LatMaterial::class, 'lat_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'lat_id');
    }

   public function investmentPools()
{
    return $this->hasMany(\App\Models\InvestmentPool::class, 'lat_id');
}

    public function summary()
    {
        return $this->hasOne(Summary::class, 'lat_id');
    }

    public function summaries()
    {
        return $this->hasMany(Summary::class, 'lat_id');
    }

    /**
     * Scope to filter lats based on user ownership
     */
    public function scopeForUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        
        // If no user, return empty
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Super Admin sees all lats
        if ($user->role === 'Super Admin') {
            return $query;
        }
        
        // Agency Owners and regular users only see their own lats
        return $query->where('user_id', $user->id);
    }
    
    /**
     * Check if current user can view lats
     */
    public static function userCanViewLats(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $user !== null;
    }
    
    /**
     * Check if user owns this lat
     */
    public function isOwnedBy(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }
        
        if ($user->role === 'Super Admin') {
            return true;
        }
        
        return $this->user_id === $user->id;
    }
    
    /**
     * Check if user can manage (edit/delete) this lat
     */
    public function canBeManagedBy(?User $user = null): bool
    {
        return $this->isOwnedBy($user);
    }

    protected $appends = ['total_price'];

    public function getTotalPriceAttribute()
    {
        // Calculate material total
        $materialTotal = $this->materials()->sum('price');
        
        // Calculate expense total
        $expenseTotal = $this->expenses()->sum('price');
        
        // Return the sum of both
        return $materialTotal + $expenseTotal;
    }

}
