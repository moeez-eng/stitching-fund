<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'role',
        'invited_by',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}