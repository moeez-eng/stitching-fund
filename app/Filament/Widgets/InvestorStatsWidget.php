<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;





class InvestorStatsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Investor';
    }
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        $walletAmount = DB::table('wallets')
            ->where('investor_id', $user->id)
            ->value('total_deposits') ?? 0;
        
        $totalInvested = DB::table('lats')
            ->where('user_id', $user->id)
            ->sum('total_price') ?? 0;
        
        $pendingPayments = DB::table('lats')
            ->where('user_id', $user->id)
            ->where('payment_status', 'pending')
            ->sum('total_price') ?? 0;
        
        $completedPayments = DB::table('lats')
            ->where('user_id', $user->id)
            ->where('payment_status', 'complete')
            ->count();
        
        return [
            Stat::make('Wallet Balance', number_format($walletAmount, 0))
                ->description('Current balance')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('primary'),
                
            Stat::make('Total Invested', number_format($totalInvested, 0))
                ->description('Lifetime investments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Pending Payments', number_format($pendingPayments, 0))
                ->description('Awaiting payment')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingPayments > 0 ? 'warning' : 'success'),
                
            Stat::make('Completed Payments', $completedPayments)
                ->description('Paid investments')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('info'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}