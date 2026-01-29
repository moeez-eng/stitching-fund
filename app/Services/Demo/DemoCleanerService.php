<?php

namespace App\Services\Demo;

use App\Models\Wallet;
use App\Models\InvestmentPool;
use App\Models\WalletLedger;
use App\Models\Lat;
use App\Models\Design;

class DemoCleanerService
{
    public function wipe($user): void
    {
        Wallet::where('user_id', $user->id)->where('is_demo', true)->delete();
        InvestmentPool::where('user_id', $user->id)->where('is_demo', true)->delete();
        WalletLedger::where('user_id', $user->id)->where('is_demo', true)->delete();
        Lat::where('user_id', $user->id)->where('is_demo', true)->delete();
        Design::where('is_demo', true)->delete();
    }
}
