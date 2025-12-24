<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;


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
        'company_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        // Allow all authenticated users to access the Filament admin panel
        return true;
    }

    /**
     * Check if the user is an agency owner or admin
     *
     * @return bool
     */
    public function isAgencyOwner(): bool
    {
        return $this->role === 'Agency Owner' || $this->role === 'Super Admin';
    }
    
    public function invitedUsers()
    {
        return $this->hasMany(User::class, 'invited_by');
    }
    
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}