<?php

namespace App\Filament\Resources\Wallet\Pages;

use Filament\Tables;
use Filament\Actions;
use App\Models\Wallet;
use Filament\Tables\Table;
use App\Models\WalletLedger;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Filament\Resources\Wallet\WalletResource;

class TransactionHistory extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string $resource = WalletResource::class;
    
    public $wallet;
    public $walletId;
    public $walletBalance;
    public $totalDeposits;
    public $totalInvestments;
    public $totalReturns;
    public $totalWithdrawals;

    public function getTitle(): string
    {
        $user = Auth::user();
        if ($user->role === 'Investor') {
            return 'My Transaction History';
        } elseif ($user->role === 'Agency Owner') {
            return 'Investors Transaction History';
        }
        return 'Transaction History';
    }

    public function getView(): string
    {
        return 'filament.wallet.pages.transaction-history';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back_to_wallet')
                ->label('Back to Wallet')
                ->icon('heroicon-o-arrow-left')
                ->url(WalletResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function mount($walletId = null)
    {
        $this->walletId = $walletId;
        $this->loadWalletData();
    }

    protected function loadWalletData()
    {
        $user = Auth::user();
        
        if ($this->walletId) {
            // Specific wallet requested
            $this->wallet = Wallet::with(['investor', 'agencyOwner'])->find($this->walletId);
            
            if (!$this->wallet) {
                return;
            }
            
            // Check permissions
            if ($user->role === 'Investor' && $this->wallet->investor_id !== $user->id) {
                abort(403);
            }
            
            if ($user->role === 'Agency Owner' && $this->wallet->agency_owner_id !== $user->id) {
                abort(403);
            }
            
            $this->calculateWalletStats();
        } else {
            // Show all wallets for current user
            if ($user->role === 'Investor') {
                $this->wallet = Wallet::where('investor_id', $user->id)->first();
            } elseif ($user->role === 'Agency Owner') {
                // For agency owner, we'll handle multiple wallets in the table
                $this->wallet = null;
            }
            
            if ($this->wallet) {
                $this->calculateWalletStats();
            }
        }
    }

    protected function calculateWalletStats()
    {
        if (!$this->wallet) {
            return;
        }

        $this->walletBalance = $this->wallet->available_balance;
        $this->totalDeposits = WalletLedger::where('wallet_id', $this->wallet->id)
            ->where('type', 'deposit')
            ->sum('amount');
        $this->totalInvestments = WalletLedger::where('wallet_id', $this->wallet->id)
            ->where('type', 'invest')
            ->sum('amount');
        $this->totalReturns = WalletLedger::where('wallet_id', $this->wallet->id)
            ->whereIn('type', ['return', 'profit'])
            ->sum('amount');
        $this->totalWithdrawals = WalletLedger::where('wallet_id', $this->wallet->id)
            ->where('type', 'withdrawal')
            ->sum('amount');
    }

    protected function getTransactionStatus($transaction): string
    {
        // This would come from your database
        // For now, return empty or mock status
        return '';
    }

    protected function getTransactionIcon($type): string
    {
        $icons = [
            'deposit' => '<i class="fas fa-arrow-down"></i>',
            'invest' => '<i class="fas fa-arrow-up"></i>',
            'return' => '<i class="fas fa-arrow-rotate-left"></i>',
            'profit' => '<i class="fas fa-star"></i>',
            'withdrawal' => '<i class="fas fa-arrow-up"></i>',
            'pool_adjustment' => '<i class="fas fa-cog"></i>'
        ];
        
        return $icons[$type] ?? '<i class="fas fa-circle-question"></i>';
    }

    protected function getTransactionLabel($type): string
    {
        $labels = [
            'deposit' => 'Deposit',
            'invest' => 'Investment',
            'return' => 'Return',
            'profit' => 'Profit',
            'withdrawal' => 'Withdrawal',
            'pool_adjustment' => 'Adjustment'
        ];
        
        return $labels[$type] ?? $type;
    }

    public function table(Table $table): Table
    {
        $user = Auth::user();
        
        $query = WalletLedger::query()->with(['wallet.investor', 'wallet.agencyOwner']);
        
        // Filter based on user role
        if ($user->role === 'Investor') {
            $query->whereHas('wallet', function ($q) use ($user) {
                $q->where('investor_id', $user->id);
            });
            
            // If specific wallet is selected, filter by that wallet
            if ($this->walletId) {
                $query->where('wallet_id', $this->walletId);
            }
        } elseif ($user->role === 'Agency Owner') {
            $query->whereHas('wallet', function ($q) use ($user) {
                $q->where('agency_owner_id', $user->id);
            });
            
            // If specific wallet is selected, filter by that wallet
            if ($this->walletId) {
                $query->where('wallet_id', $this->walletId);
            }
        }
        
        return $table
            ->query($query->orderBy('transaction_date', 'desc'))
            ->columns([
                Tables\Columns\TextColumn::make('transaction_date')
                    ->label('Date & Time')
                    ->dateTime('M d, Y h:i A')
                    ->sortable()
                    ->description(fn ($record): string => $record->created_at->diffForHumans())
                    ->weight('medium'),
                    
                TextColumn::make('wallet.investor.name')
                    ->label('Investor')
                    ->visible(fn () => Auth::user()->role !== 'Investor' || !$this->walletId)
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                                       ->color(fn ($record): string => match ($record->type) {
                        'deposit' => '#22c55e',      // Green - same as wallet balance
                        'invest' => '#f59e0b',       // Orange - same as wallet low status  
                        'return' => '#3b82f6',       // Blue - professional
                        'profit' => '#8b5cf6',       // Purple - same as wallet theme
                        'withdrawal' => '#ef4444',    // Red - same as wallet critical
                        'pool_adjustment' => '#6b7280', // Gray - neutral
                        default => '#9ca3af',
                    })
                    ->formatStateUsing(fn ($record): string => $this->getTransactionLabel($record->type)),
                    
               TextColumn::make('amount')
                    ->label('Amount (PKR)')
                    ->money('PKR')
                    ->sortable()
                    ->weight('bold')
                    ->size('lg')
                    ->color(fn ($record): string => match ($record->type) {
                        'deposit', 'return', 'profit' => '#22c55e',    // Green - same as wallet balance
                        'invest', 'withdrawal' => '#ef4444',          // Red - same as wallet critical
                        'pool_adjustment' => '#f59e0b',               // Orange - same as wallet low status
                        default => '#6b7280',
                    }),
                    
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->tooltip(fn ($record): string => $record->description)
                    ->searchable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Transaction Type')
                    ->options([
                        'deposit' => 'Deposit',
                        'invest' => 'Investment',
                        'return' => 'Return',
                        'profit' => 'Profit',
                        'withdrawal' => 'Withdrawal',
                        'pool_adjustment' => 'Adjustment',
                    ]),
                    
               SelectFilter::make('wallet_id')
                    ->label('Wallet')
                    ->visible(fn () => Auth::user()->role === 'Agency Owner' && !$this->walletId)
                    ->options(function () {
                        $user = Auth::user();
                        if ($user->role === 'Agency Owner') {
                            return Wallet::where('agency_owner_id', $user->id)
                                ->with('investor')
                                ->get()
                                ->pluck('investor.name', 'id')
                                ->toArray();
                        }
                        return [];
                    })
                    ->searchable(),
            ])
            ->defaultSort('transaction_date', 'desc')
            ->poll('60s') // Auto-refresh every 60 seconds
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('No transactions found')
            ->emptyStateDescription('No transactions have been recorded yet.')
            ->emptyStateIcon('heroicon-o-banknotes')
            ->emptyStateActions([
                Actions\Action::make('back_to_wallet')
                    ->label('Back to Wallet')
                    ->icon('heroicon-o-arrow-left')
                    ->url(WalletResource::getUrl('index'))
                    ->color('gray'),
            ]);
    }
}
