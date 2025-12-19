<?php

namespace Database\Seeders;

use App\Models\LatLabour;
use App\Models\Lat;
use Illuminate\Database\Seeder;

class LatLabourSeeder extends Seeder
{
    public function run()
    {
        $lat = Lat::first();
        
        if ($lat) {
            LatLabour::create([
                'lat_id' => $lat->id,
                'dated' => now(),
                'labour_type' => 'Stitch',
                'unit' => 'Piece',
                'rate' => 50.00,
                'pieces' => 100,
                'price' => 5000.00,
            ]);

            LatLabour::create([
                'lat_id' => $lat->id,
                'dated' => now(),
                'labour_type' => 'Embroidery',
                'unit' => 'Piece',
                'rate' => 25.00,
                'pieces' => 100,
                'price' => 2500.00,
            ]);
        }
    }
}
