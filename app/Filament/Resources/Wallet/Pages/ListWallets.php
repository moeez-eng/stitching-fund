<?php

namespace App\Filament\Resources\Wallet\Pages;

use Filament\Actions;
use App\Filament\Resources\Wallet\WalletResource;
use Filament\Resources\Pages\Page;
use App\Models\Wallet as WalletModel;
use App\Models\InvestmentPool;
use App\Models\WithdrawalRequest;
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
    public $poolStatus = 'all'; // 'all', 'open', 'active', or 'closed'

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
        
        // Get all pools with their status
        $pools = Cache::remember('investment_pools_all', 300, function () {
            return InvestmentPool::all();
        });
        
        // Filter pools based on status if not 'all'
        if ($this->poolStatus !== 'all') {
            $pools = $pools->filter(function ($pool) {
                return $pool->status === $this->poolStatus;
            });
        }
        
        // Store the filtered pools in a view variable
        view()->share('pools', $pools);
        
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
        $this->loadData();
    }
    
    public function loadData()
    {
        // This method is called by wire:poll every 5 seconds
        // The data will be refreshed in the view automatically
    }
    
    public function updatedPoolStatus($value)
    {
        // This method will be called when the pool status filter changes
        // The view will automatically update with the filtered pools
    }
}
