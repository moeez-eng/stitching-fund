<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lat extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat_no',
        'design_name',
        'customer_name',
    ];

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function customer()
    {
        return $this->belongsTo(Contact::class);
    }

public function materials()
{
    return $this->hasMany(LatMaterial::class, 'lat_id');
}

public function labours()
{
    return $this->hasMany(LatLabour::class, 'lat_id');
}

public function expenses()
{
    return $this->hasMany(Expense::class, 'lat_id');
}

}
