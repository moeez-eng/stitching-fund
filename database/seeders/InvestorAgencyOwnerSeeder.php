<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class InvestorAgencyOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set agency_owner_id for all investors to agency owner ID 2 (moiz)
        User::where('role', 'Investor')->update(['agency_owner_id' => 2]);
        
        $this->command->info('Investor agency_owner_id relationships updated successfully.');
    }
}
