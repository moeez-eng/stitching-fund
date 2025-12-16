<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lots extends Model
{
    use HasFactory;

    protected $fillable = [
        'lot_no',
        'design_id',
        'customer_id',
    ];

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class);
    }
}
