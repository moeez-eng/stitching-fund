<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\NewUserWaitingApproval;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'invited_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted()
    {
        static::created(function ($user) {
            // Send notification to all Super Admin users when a new user is created
            $superAdmins = self::where('role', 'Super Admin')->get();
            
            foreach ($superAdmins as $admin) {
                $admin->notify(new NewUserWaitingApproval($user));
            }
        });
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return true;
    }

    /**
     * Check if the user is an admin (returns 1 for admin, 0 for non-admin)
     */
    public function isAdmin(): int
    {
        return ($this->role === 'Agency Owner' || $this->role === 'Super Admin') ? 1 : 0;
    }

    /**
     * Check if the user is an agency owner or admin
     */
    public function isAgencyOwner(): bool
    {
        return $this->role === 'Agency Owner' || $this->role === 'Super Admin';
    }
    
    /**
     * Scope to filter users based on current user's role
     */
    public function scopeForCurrentUser(Builder $query, ?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        
        // If no authenticated user, show nothing
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        
        // Super Admin sees all users
        if ($user->role === 'Super Admin') {
            return $query;
        }
        
        // Agency Owner sees only themselves
        if ($user->role === 'Agency Owner') {
            return $query->where('id', $user->id);
        }
        
        // Regular users only see themselves
        return $query->where('id', $user->id);
    }
    
    /**
     * Check if current user can view this user record
     */
    public function canBeViewedBy(?User $viewer = null): bool
    {
        $viewer = $viewer ?? Auth::user();
        
        if (!$viewer) {
            return false;
        }
        
        // Super Admin can view anyone
        if ($viewer->role === 'Super Admin') {
            return true;
        }
        
        // Agency Owner and Regular users can only view themselves
        return $this->id === $viewer->id;
    }
    
    /**
     * Check if current user can edit this user record
     */
    public function canBeEditedBy(?User $editor = null): bool
    {
        $editor = $editor ?? Auth::user();
        
        if (!$editor) {
            return false;
        }
        
        // Super Admin can edit anyone
        if ($editor->role === 'Super Admin') {
            return true;
        }
        
        // Agency Owner and Regular users can only edit themselves
        return $this->id === $editor->id;
    }
    
    /**
     * Check if current user can delete this user record
     */
    public function canBeDeletedBy(?User $deleter = null): bool
    {
        $deleter = $deleter ?? Auth::user();
        
        if (!$deleter) {
            return false;
        }
        
        // Can't delete yourself
        if ($this->id === $deleter->id) {
            return false;
        }
        
        // Only Super Admin can delete users
        if ($deleter->role === 'Super Admin') {
            return true;
        }
        
        // Agency Owners and Regular users can't delete anyone
        return false;
    }
    
    public function agencyOwner()
    {
        return $this->belongsTo(User::class, 'agency_owner_id');
    }

    public function investors()
    {
        return $this->hasMany(User::class, 'agency_owner_id');
    }
}