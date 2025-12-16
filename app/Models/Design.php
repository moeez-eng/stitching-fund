<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Design extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    public function lots()
    {
        return $this->hasMany(Lots::class, 'design_id');
    }
}
