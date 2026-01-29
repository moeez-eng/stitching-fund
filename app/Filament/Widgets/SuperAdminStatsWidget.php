<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;




class SuperAdminStatsWidget extends StatsOverviewWidget
{ 
    protected ?string $pollingInterval = '30s';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Super Admin';
    }
    
    protected function getStats(): array
    {
        $totalUsers = DB::table('users')->count();
        $totalInvestors = DB::table('users')->where('role', 'Investor')->count();
        $totalAgencyOwners = DB::table('users')->where('role', 'Agency Owner')->count();
        
          $pendingApprovals = DB::table('users')
            ->where('status', 'pending')
            ->orWhere('status', 'inactive')
            ->count();
            
        return [    
            Stat::make('Total Users', $totalUsers)
                ->description('All registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
                
            Stat::make('Investors', $totalInvestors)
                ->description('Active investors')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Agency Owners', $totalAgencyOwners) 
                ->description('Registered agencies')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('warning'),
            Stat::make('Pending Approvals', $pendingApprovals)
                ->description('Users awaiting approval')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($pendingApprovals > 0 ? 'warning' : 'success'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}