<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invitation extends Model
{
    protected $fillable = [
        'lot_no',
        'labour_type',
        'unit',
        'rate',
        'pieces',
        'price',
        'dated',
    ];

    protected $casts = [
        'dated' => 'date',
        'unit' => 'decimal:2',
        'rate' => 'decimal:2',
        'pieces' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function lot(): BelongsTo
    {
        return $this->belongsTo(Lots::class, 'lot_no');
    }
}