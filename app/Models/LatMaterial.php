<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LatMaterial extends Model
{
    protected $fillable = [
        'lat_id',
        'material',
        'colour',
        'unit',
        'rate',
        'quantity',
        'price',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function lot()
    {
        return $this->belongsTo(Lots::class, 'lat_id');
    }
}