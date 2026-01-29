<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;




class AgencyOwnerStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Agency Owner';
    }
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        $totalInvestors = DB::table('wallets')
            ->where('agency_owner_id', $user->id)
            ->distinct('investor_id')
            ->count();
        
        $totalInvestments = DB::table('wallets')
            ->where('agency_owner_id', $user->id)
            ->sum('total_deposits') ?? 0;
        
        $pendingWithdrawals = DB::table('withdrawal_requests')
            ->join('wallets', 'wallets.id', 'withdrawal_requests.wallet_id')
            ->where('wallets.agency_owner_id', $user->id)
            ->where('withdrawal_requests.status', 'pending')
            ->count();
        
        $activeLats = DB::table('lats')
            ->join('users', 'users.id', 'lats.user_id')
            ->join('wallets', 'wallets.investor_id', 'users.id')
            ->where('wallets.agency_owner_id', $user->id)
            ->count();
        
        return [
            Stat::make('Total Investors', $totalInvestors)
                ->description('Investors under your agency')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('Total Investments', number_format($totalInvestments, 0))
                ->description('Total amount invested')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Pending Withdrawals', $pendingWithdrawals)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-arrow-down-circle')
                ->color($pendingWithdrawals > 0 ? 'warning' : 'success'),
                
            Stat::make('Active Investments', $activeLats)
                ->description('Active LAT investments')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}