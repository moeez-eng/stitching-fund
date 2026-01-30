<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;

class Design extends Model
{
    
    protected static function booted()
    {
        static::creating(function ($design) {
            if (Auth::check() && !$design->user_id) {
                $design->user_id = Auth::id();
            }
        });
    }
    
    protected $fillable = [
        'name',
        'user_id',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function lats()
    {
        return $this->hasMany(Lat::class, 'design_id');
    }
    
    /**
     * Scope to filter designs based on user ownership
     */
    public function scopeForUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        
        // If no user, return empty
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Super Admin sees all designs
        if ($user->role === 'Super Admin') {
            return $query;
        }
        
        // Agency Owners and regular users only see their own designs
        return $query->where('user_id', $user->id);
    }
    
    /**
     * Check if current user can view designs
     */
    public static function userCanViewDesigns(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $user !== null;
    }
    
    /**
     * Check if user owns this design
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
     * Check if user can manage (edit/delete) this design
     */
    public function canBeManagedBy(?User $user = null): bool
    {
        return $this->isOwnedBy($user);
    }
}
