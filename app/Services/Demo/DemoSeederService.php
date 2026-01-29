<?php

namespace App\Services\Demo;

use App\Models\Lat;
use App\Models\Design;
use App\Models\Wallet;
use App\Models\InvestmentPool;

class DemoSeederService
{
    public function seedForUser($user): void
    {
        // Safety: agar pehle se demo data hai
        if (Wallet::where('investor_id', $user->id)->where('is_demo', true)->exists()) {
            return;
        }

        // 💰 Demo Wallet
        $wallet = Wallet::create([
            'investor_id' => $user->id,
            'total_deposits' => 100000,
            'amount' => 60000,
            'is_demo' => true,
        ]);

        // 🏦 Demo Pools
        $pools = InvestmentPool::factory()->count(3)->create([
            'user_id' => $user->id,
            'status' => 'active',
            'is_demo' => true,
        ]);

        // 💸 Demo Transactions (using WalletLedger instead)
        foreach ($pools as $pool) {
            \App\Models\WalletLedger::create([
                'wallet_id' => $wallet->id,
                'amount' => 20000,
                'type' => 'investment',
                'description' => 'Demo investment to pool',
                'reference_id' => $pool->id,
                'reference_type' => 'investment_pool',
            ]);
        }

        // 🧱 Demo LATs
        $lats = Lat::factory()->count(2)->create([
            'user_id' => $user->id,
            'is_demo' => true,
        ]);

        // 🎨 Demo Designs
        foreach ($lats as $lat) {
            Design::factory()->count(2)->create([
                'lat_id' => $lat->id,
                'is_demo' => true,
            ]);
        }
    }
}
