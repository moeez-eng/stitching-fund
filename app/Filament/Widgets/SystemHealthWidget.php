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
        $cacheStatus = 'Active';
        $cacheColor = 'success';
        try {
            cache()->put('test_connection', true, 10);
            if (!cache()->get('test_connection')) {
                throw new \Exception('Cache not working');
            }
        } catch (\Exception $e) {
            $cacheStatus = 'Inactive';
            $cacheColor = 'danger';
        }

        // Disk usage with error handling
        $diskUsedPercent = 'N/A';
        $diskColor = 'info';
        try {
            $diskTotal = disk_total_space(base_path());
            $diskFree = disk_free_space(base_path());
            if ($diskTotal && $diskFree) {
                $diskUsedPercent = round((($diskTotal - $diskFree) / $diskTotal) * 100) . '%';
                $diskColor = $diskUsedPercent > 90 ? 'danger' : ($diskUsedPercent > 75 ? 'warning' : 'success');
            }
        } catch (\Exception $e) {
            // Keep default values
        }

        // Memory usage with error handling
        $memoryUsagePercent = 'N/A';
        $memoryColor = 'info';
        try {
            if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && file_exists('/proc/meminfo')) {
                $meminfo = file('/proc/meminfo');
                if ($meminfo && isset($meminfo[0]) && isset($meminfo[2])) {
                    $memTotal = (int) filter_var($meminfo[0], FILTER_SANITIZE_NUMBER_INT);
                    $memAvailable = (int) filter_var($meminfo[2], FILTER_SANITIZE_NUMBER_INT);
                    if ($memTotal > 0) {
                        $memUsed = $memTotal - $memAvailable;
                        $memoryUsagePercent = round(($memUsed / $memTotal) * 100) . '%';
                        $memoryColor = $memoryUsagePercent > 90 ? 'danger' : 'success';
                    }
                }
            }
        } catch (\Exception $e) {
            // Keep default values
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
                ->color($diskColor),

            Stat::make('Memory Usage', $memoryUsagePercent)
                ->description('Server memory')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color($memoryColor),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
}