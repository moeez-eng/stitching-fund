<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;


class SystemHealthWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Super Admin';
    }
    
  protected function getStats(): array
{
    // Database status
    $dbStatus = 'Online';
    try {
        DB::connection()->getPdo();
    } catch (\Exception $e) {
        $dbStatus = 'Offline';
    }

    // Cache status
    $cacheStatus = 'Inactive';
    $cacheColor = 'danger';
    try {
        if (cache()->store()->put('test_connection', true, 10)) {
            $cacheStatus = 'Active';
            $cacheColor = 'success';
        }
    } catch (\Exception $e) {}

    // Disk usage
    $diskTotal = disk_total_space('/');
    $diskFree = disk_free_space('/');
    $diskUsedPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100) . '%';

    // Memory usage (Linux)
    $memoryUsagePercent = 'N/A';
    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
        $meminfo = file('/proc/meminfo');
        $memTotal = (int) filter_var($meminfo[0], FILTER_SANITIZE_NUMBER_INT);
        $memAvailable = (int) filter_var($meminfo[2], FILTER_SANITIZE_NUMBER_INT);
        $memUsed = $memTotal - $memAvailable;
        $memoryUsagePercent = round(($memUsed / $memTotal) * 100) . '%';
    }

    return [
        Stat::make('Database', $dbStatus)
            ->description('Database connection')
            ->descriptionIcon($dbStatus === 'Online' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
            ->color($dbStatus === 'Online' ? 'success' : 'danger'),

        Stat::make('Cache', $cacheStatus)
            ->description('Cache system')
            ->descriptionIcon('heroicon-m-server')
            ->color($cacheColor),

        Stat::make('Disk Usage', $diskUsedPercent)
            ->description('Server disk space')
            ->descriptionIcon('heroicon-m-circle-stack')
            ->color($diskUsedPercent > 90 ? 'danger' : ($diskUsedPercent > 75 ? 'warning' : 'success')),

        Stat::make('Memory Usage', $memoryUsagePercent)
            ->description('Server memory')
            ->descriptionIcon('heroicon-m-cpu-chip')
            ->color($memoryUsagePercent === 'N/A' ? 'info' : (intval($memoryUsagePercent) > 90 ? 'danger' : 'success')),
    ];
}

    
    protected function getColumns(): int
    {
        return 4;
    }
}