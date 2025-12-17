<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotMaterial extends Model
{
    protected $fillable = [
        'lot_id',
        'dated',
        'material',
        'colour',
        'unit',
        'rate',
        'quantity',
        'price',
    ];

    protected $casts = [
        'dated' => 'datetime',
        'rate' => 'decimal:2',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lots::class, 'lot_id');
    }
}
