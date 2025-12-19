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
        'dated',
    ];

    protected $casts = [
        'rate' => 'integer',
        'quantity' => 'integer',
        'price' => 'integer',
    ];

    public function lot()
    {
        return $this->belongsTo(Lots::class, 'lat_id');
    }
}