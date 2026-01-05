<?php

namespace App\Filament\Resources\InvestmentPool\Actions;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletAllocation;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class AddInvestorAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'addInvestor';
    }

    public function handle(array $data, $record): void
    {
        $investor = User::findOrFail($data['investor_id']);
        $amount = (float) $data['amount'];

        DB::transaction(function () use ($investor, $amount, $record) {
            // Check if pool is already full
            if ($record->total_collected >= $record->amount_required) {
                throw new \Exception('This pool is already fully funded.');
            }

            // Check if investor has a wallet
            $wallet = Wallet::where('investor_id', $investor->id)->first();
            
            if (!$wallet) {
                throw new \Exception('Investor does not have a wallet. Please create a wallet for this investor first.');
            }

            // Check available balance
            if ($wallet->available_balance < $amount) {
                throw new \Exception('Insufficient funds in investor\'s wallet.');
            }

            // Calculate actual investment amount (can't exceed remaining pool amount)
            $remainingAmount = $record->amount_required - $record->total_collected;
            $actualAmount = min($amount, $remainingAmount);

            // Deduct from wallet
            $wallet->decrement('amount', $actualAmount);

            // Add to pool's collected amount
            $record->increment('total_collected', $actualAmount);

            // Create wallet allocation
            WalletAllocation::create([
                'wallet_id' => $wallet->id,
                'investor_id' => $investor->id,
                'investment_pool_id' => $record->id,
                'amount' => $actualAmount,
                'status' => 'invested',
            ]);

            // Update pool status if fully funded
            if ($record->total_collected >= $record->amount_required) {
                $record->update(['status' => 'fully_funded']);
            }
        });

        Notification::make()
            ->title('Investment successful')
            ->success()
            ->send();
    }

    public static function getFormSchema(): array 
    {
        $currentUser = \Filament\Facades\Filament::auth()->user();
        
        // Get investors who are either invited by the current agency owner or have the current agency owner as their inviter
        $query = User::where('role', 'Investor');
        
        if ($currentUser->role === 'Agency Owner') {
            $query->where('invited_by', $currentUser->id);
        } elseif ($currentUser->role === 'Super Admin') {
            // Super admin can see all investors with wallets
            $query->whereHas('wallet');
        }
        
        $investors = $query->with('wallet')
            ->get()
            ->mapWithKeys(fn ($user) => [
                $user->id => $user->name . ' (Wallet: PKR ' . number_format($user->wallet?->available_balance ?? 0, 2) . ')'
            ]);

        return [
            Select::make('investor_id')
                ->label('Select Investor')
                ->options($investors)
                ->searchable()
                ->required()
                ->preload()
                ->helperText('Only investors with available wallet balance are shown'),
                
            TextInput::make('amount')
                ->label('Investment Amount (PKR)')
                ->numeric()
                ->minValue(1)
                ->required()
                ->prefix('PKR')
                ->helperText('Enter the amount to invest in this pool'),
        ];
    }
}
