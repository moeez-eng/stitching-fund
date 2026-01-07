<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
    'email',
    'token',
    'role',
    'invited_by',
    'company_name',
    'unique_code',
    'accepted_at',
    'expires_at',
    'status',
    'user_id'
];

    protected $casts = [
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship to the user who sent the invitation
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Relationship to the user who accepted the invitation
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->expires_at->isPast();
    }
    
    /**
     * Check if the invitation is valid (pending, not expired, and not accepted)
     */
    public function isValid(): bool
    {
        return $this->status === 'pending' && 
               $this->expires_at->isFuture() && 
               !$this->accepted_at;
    }

    /**
     * Check if invitation has been accepted
     */


    /**
     * Check if invitation is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired() && !$this->isAccepted();
    }

    /**
     * Mark the invitation as accepted
     */
    public function markAsAccepted(User $user): bool
    {
        return $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
            'user_id' => $user->id
        ]);
    }

    /**
     * Mark the invitation as expired
     */
    public function markAsExpired(): bool
    {
        if ($this->isPending()) {
            return $this->update(['status' => 'expired']);
        }
        return false;
    }

    /**
     * Scope a query to only include pending invitations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                    ->where('expires_at', '>', now());
    }

    /**
     * Scope a query to only include accepted invitations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope a query to only include expired invitations.
     */
    public function scopeExpired($query)
    {
        return $query->where(function($q) {
            $q->where('status', 'expired')
              ->orWhere('expires_at', '<=', now());
        });
    }

    /**
     * Check if the invitation has been accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted' || $this->accepted_at !== null;
    }
}