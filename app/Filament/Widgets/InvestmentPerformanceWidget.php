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
        
        // TODO: Calculate actual values
        $roi = '12.5%';
        $monthlyGrowth = '2.3%';
        $totalReturns = '$1,250.00';
        
        $investmentCount = DB::table('lats')
            ->where('user_id', $user->id)
            ->count();
        
        return [
          Stat::make('ROI', $roi)
                ->description('Return on investment')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Monthly Growth', $monthlyGrowth)
                ->description('This month')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
                
            Stat::make('Total Returns', $totalReturns)
                ->description('Profit earned')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),
                
            Stat::make('Investments', $investmentCount)
                ->description('Total investments')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}