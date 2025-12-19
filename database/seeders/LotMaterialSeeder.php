<?php

namespace Database\Seeders;

use App\Models\LatMaterial;
use App\Models\Lat;
use Illuminate\Database\Seeder;

class LatMaterialSeeder extends Seeder
{
    public function run()
    {
        $lat = Lat::first();
        
        if ($lat) {
            // Clear existing materials for this lat
            LatMaterial::where('lat_id', $lat->id)->delete();
            
            // Add sample materials
            LatMaterial::create([
                'lat_id' => $lat->id,
                'material' => 'Cotton Fabric',
                'colour' => 'White',
                'unit' => 'Meter',
                'rate' => 150.00,
                'quantity' => 10,
                'price' => 1500.00,
                'dated' => now(),
            ]);

            LatMaterial::create([
                'lat_id' => $lat->id,
                'material' => 'Thread',
                'colour' => 'Black',
                'unit' => 'Roll',
                'rate' => 50.00,
                'quantity' => 5,
                'price' => 250.00,
                'dated' => now(),
            ]);
        }
    }
}
