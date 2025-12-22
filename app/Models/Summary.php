<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat_id',
        'type',
        'amount',
    ];

    public function lat(): BelongsTo
    {
        return $this->belongsTo(Lat::class);
    }
}