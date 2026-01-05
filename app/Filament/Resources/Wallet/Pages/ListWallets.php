<?php

namespace App\Filament\Resources\Wallet\Pages;

use Filament\Actions;
use App\Filament\Resources\Wallet\WalletResource;
use Filament\Resources\Pages\Page;
use App\Models\Wallet as WalletModel;
use App\Models\InvestmentPool;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\HtmlString;
use BackedEnum;

class ListWallets extends Page
{
    protected static string $resource = WalletResource::class;
    
    protected static ?string $navigationLabel = 'Wallet';
    
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $title = 'Wallet';

    public $userName;
    public $wallet;
    public $walletStatus;
    public $availableBalance;
    public $totalInvested;
    public $user;

    public function getTitle(): string
    {
        $user = Auth::user();
        return $user->role === 'Investor' ? 'My Wallet' : 'Investor Wallets';
    }
    
    public function getView(): string
    {
        return 'filament.wallet.pages.list-wallets';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Auth::user()?->role === 'Agency Owner'),
        ];
    }


    public function getWalletData()
    {
        $user = Auth::user();
        
        // Cache investment pools for better performance
        $pools = Cache::remember('investment_pools_open', 300, function () {
            return InvestmentPool::where('status', 'open')->get();
        });
        
        if ($user->role === 'Investor') {
            // Single investor view
            $wallet = WalletModel::where('investor_id', $user->id)
                ->with(['investor', 'agencyOwner', 'allocations'])
                ->first();
            
            if (!$wallet) {
                return null;
            }

            return [$wallet];
        }
        
        // Agency Owner view - all investors
        $wallets = WalletModel::where('agency_owner_id', $user->id)
            ->with(['investor', 'agencyOwner', 'allocations'])
            ->get();
            
        return $wallets;
    }

    public function mount()
    {
        $user = Auth::user();
        $wallets = $this->getWalletData();
        
        if ($wallets && isset($wallets[0])) {
            $wallet = $wallets[0];
            
            // Calculate total invested from allocations
            $totalInvested = $wallet->allocations->sum('amount') ?? 0;
            
            // Available balance is wallet amount minus total invested
            $availableBalance = $wallet->amount - $totalInvested;
            
            if ($availableBalance > 50000) {
                $status = 'healthy';
            } elseif ($availableBalance > 10000) {
                $status = 'low';
            } else {
                $status = 'critical';
            }
            
            $this->userName = $wallet->investor->name ?? $user->name;
            $this->wallet = $wallet;
            $this->walletStatus = ['status' => $status];
            $this->availableBalance = $availableBalance;
            $this->totalInvested = $totalInvested;
            $this->user = $user;
        }
    }
}
