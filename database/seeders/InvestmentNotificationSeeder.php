<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\InvestmentPool;
use App\Models\Wallet;
use App\Models\WalletAllocation;
use App\Models\Lat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvestmentNotificationSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create an agency owner
        $owner = User::where('role', 'Agency Owner')->first();
        if (!$owner) {
            $owner = User::create([
                'name' => 'Test Agency Owner',
                'email' => 'owner@example.com',
                'password' => bcrypt('password'),
                'role' => 'Agency Owner',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        // Get or create an investor
        $investor = User::where('role', 'Investor')->where('agency_owner_id', $owner->id)->first();
        if (!$investor) {
            $investor = User::create([
                'name' => 'Test Investor',
                'email' => 'investor@example.com',
                'password' => bcrypt('password'),
                'role' => 'Investor',
                'status' => 'active',
                'agency_owner_id' => $owner->id,
                'email_verified_at' => now(),
            ]);
        }

        // Create a test LAT if none exists
        $lat = Lat::first();
        if (!$lat) {
            $lat = Lat::create([
                'design_name' => 'Test Design',
                'design_code' => 'TEST001',
                'user_id' => $owner->id,
            ]);
        }

        // Create a test investment pool (this will trigger NewInvestmentPoolCreated notification)
        $investmentPool = InvestmentPool::create([
            'lat_id' => $lat->id,
            'design_name' => 'Test Investment Pool',
            'amount_required' => 100000,
            'number_of_partners' => 5,
            'user_id' => $owner->id,
        ]);

        // Create a wallet for the investor
        $wallet = Wallet::create([
            'agency_owner_id' => $owner->id,
            'investor_id' => $investor->id,
            'amount' => 50000,
            'slip_type' => 'deposit',
            'reference' => 'TEST-DEPOSIT-' . Str::random(6),
            'deposited_at' => now(),
        ]);

        // Create a wallet allocation (this will trigger WalletAmountDeducted notification)
        WalletAllocation::create([
            'investor_id' => $investor->id,
            'investment_pool_id' => $investmentPool->id,
            'amount' => 25000,
        ]);

        $this->command->info('Investment notifications seeded successfully!');
        $this->command->info('Owner: ' . $owner->email);
        $this->command->info('Investor: ' . $investor->email);
        $this->command->info('Check notifications for the investor account.');
    }
}
