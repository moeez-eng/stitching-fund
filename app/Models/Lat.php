<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Lat extends Model
{
    use HasFactory;


    protected static function booted()
    {
        static::creating(function ($lat) {
            if (Auth::check() && !$lat->user_id) {
                $lat->user_id = Auth::id();
            }
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
        
        // Log the values for debugging
        Log::info('Material Total: ' . $materialTotal);
        Log::info('Expense Total: ' . $expenseTotal);
        Log::info('Combined Total: ' . ($materialTotal + $expenseTotal));
        
        // Return the sum of both
        return $materialTotal + $expenseTotal;
    }

}
