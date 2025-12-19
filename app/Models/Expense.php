<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat_id',
        'labour_type',
        'unit',
        'rate',
        'pieces',
        'price',
        'dated',
    ];

    protected $casts = [
        'dated' => 'date',
    ];

    public function lat()
    {
        return $this->belongsTo(Lat::class, 'lat_id');
    }
}
