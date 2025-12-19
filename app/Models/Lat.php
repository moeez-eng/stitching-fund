<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lat extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat_no',
        'design_name',
        'customer_name',
        'total_price',
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

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'lat_id');
    }

    protected $appends = ['total_price'];

    public function getTotalPriceAttribute()
    {
        // Calculate material total
        $materialTotal = $this->materials()->sum('price');
        
        // Calculate expense total
        $expenseTotal = $this->expenses()->sum('price');
        
        // Log the values for debugging
        Log::info('Material Total: ' . $materialTotal);
        Log::info('Expense Total: ' . $expenseTotal);
        Log::info('Combined Total: ' . ($materialTotal + $expenseTotal));
        
        // Return the sum of both
        return $materialTotal + $expenseTotal;
    }

}
