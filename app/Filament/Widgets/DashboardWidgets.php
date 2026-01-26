<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Lat;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\TableWidget as BaseTableWidget;

// Super Admin Stats Widget
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
        ];
    }
    
    protected function getColumns(): int
    {
        return 3;
    }
}

// Agency Owner Stats Widget
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
            ->sum('amount') ?? 0;
        
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
                
            Stat::make('Total Investments', '$' . number_format($totalInvestments, 2))
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

// Investor Stats Widget
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
            ->value('amount') ?? 0;
        
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
            Stat::make('Wallet Balance', '$' . number_format($walletAmount, 2))
                ->description('Current balance')
                ->descriptionIcon('heroicon-m-wallet')
                ->color('primary'),
                
            Stat::make('Total Invested', '$' . number_format($totalInvested, 2))
                ->description('Lifetime investments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
                
            Stat::make('Pending Payments', '$' . number_format($pendingPayments, 2))
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

// System Health Widget (Super Admin only)
class SystemHealthWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Super Admin';
    }
    
    protected function getStats(): array
    {
        $dbStatus = 'Online';
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $dbStatus = 'Offline';
        }
        
        return [
            Stat::make('Database', $dbStatus)
                ->description('Database connection')
                ->descriptionIcon($dbStatus === 'Online' ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($dbStatus === 'Online' ? 'success' : 'danger'),
                
            Stat::make('Cache', 'Active')
                ->description('Cache system')
                ->descriptionIcon('heroicon-m-server')
                ->color('info'),
                
            Stat::make('Disk Usage', '85%')
                ->description('Server disk space')
                ->descriptionIcon('heroicon-m-circle-stack')
                ->color('warning'),
                
            Stat::make('Memory Usage', '62%')
                ->description('Server memory')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}

// Security Alerts Widget (Super Admin only)
class SecurityAlertsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Super Admin';
    }
    
    protected function getStats(): array
    {
        $failedLogins = DB::table('failed_jobs')
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->count();
        
        $pendingRegistrations = DB::table('user_invitations')
            ->where('status', 'pending')
            ->count();
        
        return [
            Stat::make('Failed Logins (24h)', $failedLogins)
                ->description('Security threats')
                ->descriptionIcon('heroicon-m-shield-exclamation')
                ->color($failedLogins > 10 ? 'danger' : ($failedLogins > 5 ? 'warning' : 'success')),
                
            Stat::make('Active Sessions', '42')
                ->description('Current user sessions')
                ->descriptionIcon('heroicon-m-finger-print')
                ->color('info'),
                
            Stat::make('Pending Registrations', $pendingRegistrations)
                ->description('Awaiting approval')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color($pendingRegistrations > 0 ? 'warning' : 'success'),
                
            Stat::make('Security Score', '92%')
                ->description('System security rating')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('success'),
        ];
    }
    
    protected function getColumns(): int
    {
        return 4;
    }
}

// Recent Investments Widget (Agency Owner only)
class RecentInvestmentsWidget extends BaseTableWidget
{
    protected static ?string $heading = 'Recent Investments';
    protected static ?string $description = 'Latest investments from your investors';
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Agency Owner';
    }
    
    public function table(Table $table): Table
    {
        $user = Auth::user();
        
        return $table
            ->query(
                Lat::with(['user'])
                    ->whereHas('user.wallets', function (Builder $query) use ($user) {
                        $query->where('agency_owner_id', $user->id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Investor')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('total_price')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),
                    
                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'partial' => 'warning',
                        'complete' => 'success',
                        'lose' => 'gray',
                        default => 'gray',
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ]);
    }
}

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