<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LatLabour extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat_id',
        'dated',
        'labour_type',
        'unit',
        'rate',
        'pieces',
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
