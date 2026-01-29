<?php

namespace App\Filament\Widgets;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


// Investment Performance Widget (Investor only)
class InvestmentPerformanceWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Investor';
    }
    
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Get the latest pool (most recent LAT record)
        $latestPool = DB::table('lats')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Calculate values from latest pool only
        $totalInvested = $latestPool->initial_investment ?? 0;
        $totalReceived = $latestPool->market_payments_received ?? 0;
        $totalWithProfit = $latestPool->total_with_profit ?? 0;
        
        $investmentCount = DB::table('lats')
            ->where('user_id', $user->id)
            ->count();
        
        // Calculate ROI from latest pool
        $roi = $totalInvested > 0 ? (($totalWithProfit - $totalInvested) / $totalInvested) * 100 : 0;
        $roiFormatted = number_format($roi, 2) . '%';
        
        // Calculate monthly growth (current month vs previous month) - still from all pools for growth trend
        $currentMonthStart = now()->startOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();
        
        $currentMonthReturns = DB::table('lats')
            ->where('user_id', $user->id)
            ->where('created_at', '>=', $currentMonthStart)
            ->sum('market_payments_received') ?? 0;
            
        $previousMonthReturns = DB::table('lats')
            ->where('user_id', $user->id)
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('market_payments_received') ?? 0;
        
        $monthlyGrowth = $previousMonthReturns > 0 ? (($currentMonthReturns - $previousMonthReturns) / $previousMonthReturns) * 100 : 0;
        $monthlyGrowthFormatted = number_format($monthlyGrowth, 2) . '%';
        
        // Total returns from latest pool (profit earned)
        $totalReturns = $totalWithProfit - $totalInvested;
        $totalReturnsFormatted = number_format(abs($totalReturns), 2);
        
        return [
          Stat::make('ROI', $roiFormatted)
                ->description('Latest pool return')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color($roi >= 0 ? 'success' : 'danger'),

            Stat::make('Monthly Growth', $monthlyGrowthFormatted)
                ->description('This month')
                ->color($monthlyGrowth >= 0 ? 'primary' : 'danger'),
                
            Stat::make('Latest Pool Returns', $totalReturnsFormatted)
                ->description('Profit from latest pool')
                ->color($totalReturns >= 0 ? 'info' : 'danger'),
                
            Stat::make('Latest Investment', number_format($totalInvested, 2))
                ->description('Latest pool amount')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}