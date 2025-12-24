<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserInvitation extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();
        
        // Only show invitations created by current user
        static::addGlobalScope(function ($builder) {
            if (Auth::check() && Auth::user()->role !== 'Super Admin') {
                $builder->where('invited_by', Auth::id());
            }
        });
    }


    protected $fillable = [
        'email',
        'token',
        'role',
        'invited_by',
        'company_name',
        'unique_code',
        'accepted_at',
        'expires_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public static function generateToken()
    {
        return Str::random(60);
    }

    public static function generateUniqueCode()
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('unique_code', $code)->exists());
        
        return $code;
    }

    public function getInvitationUrl()
    {
        $companySlug = Str::slug($this->company_name);
        return url("/accept-invitation/{$companySlug}/{$this->unique_code}");
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted()
    {
        return !is_null($this->accepted_at);
    }

    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }
}
