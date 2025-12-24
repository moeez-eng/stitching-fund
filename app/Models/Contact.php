<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contact extends Model
{
    use HasFactory;
    
    protected static function booted()
    {
        static::creating(function ($contact) {
            if (Auth::check() && !$contact->user_id) {
                $contact->user_id = Auth::id();
            }
        });
    }
    
    protected $fillable = [
        'name',
        'phone',
        'ctype',
        'user_id',
    ];
    
    /**
     * Relationship: Contact belongs to a user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope to filter contacts based on user ownership
     */
    public function scopeForUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        
        // If no user, return empty
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Super Admin sees all contacts
        if ($user->role === 'Super Admin') {
            return $query;
        }
        
        // Agency Owner sees their own contacts + contacts of users they invited
        if ($user->role === 'Agency Owner') {
            return $query->where(function($q) use ($user) {
                $q->where('user_id', $user->id) // Their own contacts
                  ->orWhereHas('user', function($q) use ($user) {
                      $q->where('invited_by', $user->id); // Contacts of invited users
                  });
            });
        }
        
        // Regular users only see their own contacts
        return $query->where('user_id', $user->id);
    }
    
    /**
     * Check if current user can view contacts
     */
    public static function userCanViewContacts(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        return $user !== null;
    }
    
    /**
     * Check if user owns this contact
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
     * Check if user can manage (edit/delete) this contact
     */
    public function canBeManagedBy(?User $user = null): bool
    {
        return $this->isOwnedBy($user);
    }
}