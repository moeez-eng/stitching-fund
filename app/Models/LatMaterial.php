<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LatMaterial extends Model
{
    protected $table = 'lot_materials';
    
    protected $fillable = [
        'lat_id',
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

    public function lat()
    {
        return $this->belongsTo(Lat::class, 'lat_id');
    }
}
