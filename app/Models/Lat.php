<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Lat extends Model
{
    use HasFactory;


    protected static function boot()
    {
        parent::boot();
        
        // Only show Lats from same company
        static::addGlobalScope(function ($builder) {
            if (Auth::check() && Auth::user()->role !== 'Super Admin') {
                $builder->where('company_name', Auth::user()->company_name);
            }
        });
    }

    protected $fillable = [
        'lat_no',
        'design_name',
        'customer_name',
        'total_price',
        'pieces',
        'profit_percentage',
        'initial_investment',
        'company_name',
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

    public function summary()
    {
        return $this->hasOne(Summary::class, 'lat_id');
    }

    public function summaries()
    {
        return $this->hasMany(Summary::class, 'lat_id');
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
