<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Design extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    public function lats()
    {
        return $this->hasMany(Lat::class, 'design_id');
    }
}
