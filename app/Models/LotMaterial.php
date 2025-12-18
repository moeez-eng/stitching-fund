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
    ];

    public function lot()
    {
        return $this->belongsTo(Lots::class, 'lot_id');
    }
}
