<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvestmentPool extends Model
{
    use HasFactory;

    protected $fillable = [
        'lat_id',
        'design_name',
        'amount_required',
        'number_of_partners',
        'total_collected',
        'percentage_collected',
        'remaining_amount',
        'partners',
        'user_id',
    ];

    protected $casts = [
        'amount_required' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'percentage_collected' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'partners' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function ($investmentPool) {
            // Ensure design_name is set from lat_id if not provided
            if (empty($investmentPool->design_name) && $investmentPool->lat_id) {
                $investmentPool->design_name = \App\Models\Lat::find($investmentPool->lat_id)?->design_name;
            }

            // Process partners data to include investment_percentage
            if (is_array($investmentPool->partners)) {
                $amountRequired = $investmentPool->amount_required ?? 0;
                
                $investmentPool->partners = collect($investmentPool->partners)->map(function ($partner) use ($amountRequired) {
                    if (isset($partner['investment_amount']) && $amountRequired > 0) {
                        $partner['investment_percentage'] = round(($partner['investment_amount'] / $amountRequired) * 100);
                    } else {
                        $partner['investment_percentage'] = 0;
                    }
                    return $partner;
                })->toArray();

                // Calculate total collected from partners
                $totalCollected = collect($investmentPool->partners)->sum('investment_amount');
                $investmentPool->total_collected = $totalCollected;

                // Calculate percentage collected
                if ($amountRequired > 0) {
                    $investmentPool->percentage_collected = round(($totalCollected / $amountRequired) * 100, 2);
                } else {
                    $investmentPool->percentage_collected = 0;
                }

                // Calculate remaining amount
                $investmentPool->remaining_amount = $amountRequired - $totalCollected;
            } else {
                // Reset values if no partners
                $investmentPool->total_collected = 0;
                $investmentPool->percentage_collected = 0;
                $investmentPool->remaining_amount = $investmentPool->amount_required ?? 0;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lat()
    {
        return $this->belongsTo(Lat::class);
    }
}
