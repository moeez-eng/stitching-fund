<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;

class Contact extends Model
{
    use HasFactory;
    
    protected static function boot()
    {
        parent::boot();
        
        // Only show contacts from same company
        static::addGlobalScope(function ($builder) {
            if (Auth::check() && Auth::user()->role !== 'Super Admin') {
                $builder->where('company_name', Auth::user()->company_name);
            }
        });
    }
    
    protected $fillable = [
        'name',
        'phone',
        'ctype',
        'company_name',
    ];
}
