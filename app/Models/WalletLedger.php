<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Filament\Notifications\Notification;

class WalletLedger extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'reference',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($ledger) {
            $wallet = $ledger->wallet;
            $investor = $wallet->investor;
            
            if (!$investor) return;
            
            switch ($ledger->type) {
                case self::TYPE_DEPOSIT:
                    Notification::make()
                        ->title('Deposit Successful')
                        ->body("Your wallet has been credited with {$ledger->amount}.")
                        ->success()
                        ->sendToDatabase($investor);
                    break;
                    
                case self::TYPE_INVEST:
                    Notification::make()
                        ->title('Investment Processed')
                        ->body("Investment of {$ledger->amount} has been processed.")
                        ->warning()
                        ->sendToDatabase($investor);
                    break;
                    
                case self::TYPE_RETURN:
                    Notification::make()
                        ->title('Investment Returned')
                        ->body("Amount of {$ledger->amount} has been returned to your wallet.")
                        ->success()
                        ->sendToDatabase($investor);
                    break;
                    
                case self::TYPE_PROFIT:
                    Notification::make()
                        ->title('Profit Received')
                        ->body("Profit of {$ledger->amount} has been added to your wallet.")
                        ->success()
                        ->sendToDatabase($investor);
                    break;
                    
                case self::TYPE_WITHDRAWAL:
                    Notification::make()
                        ->title('Withdrawal Processed')
                        ->body("Withdrawal of {$ledger->amount} has been processed.")
                        ->warning()
                        ->sendToDatabase($investor);
                    break;
            }
        });
    }

    // Transaction types
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_INVEST = 'invest';
    const TYPE_RETURN = 'return';
    const TYPE_PROFIT = 'profit';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_POOL_ADJUSTMENT = 'pool_adjustment';

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    /**
     * Create a deposit entry
     */
    public static function createDeposit(Wallet $wallet, float $amount, string $description = null, string $reference = null): self
    {
        return static::create([
            'wallet_id' => $wallet->id,
            'type' => self::TYPE_DEPOSIT,
            'amount' => $amount,
            'description' => $description ?? "Deposit added",
            'reference' => $reference,
            'transaction_date' => now(),
        ]);
    }

    /**
     * Create an investment entry
     */
    public static function createInvestment(Wallet $wallet, float $amount, WalletAllocation $allocation, string $description = null): self
    {
        return static::create([
            'wallet_id' => $wallet->id,
            'type' => self::TYPE_INVEST,
            'amount' => $amount,
            'description' => $description ?? "Investment in pool " . ($allocation->investmentPool->name ?? '') . "",
            'reference_type' => 'allocation',
            'reference_id' => $allocation->id,
            'transaction_date' => now(),
        ]);
    }

    /**
     * Create a return entry
     */
    public static function createReturn(Wallet $wallet, float $amount, string $description = null, string $reference = null): self
    {
        return static::create([
            'wallet_id' => $wallet->id,
            'type' => self::TYPE_RETURN,
            'amount' => $amount,
            'description' => $description ?? "Investment return",
            'reference' => $reference,
            'transaction_date' => now(),
        ]);
    }

    /**
     * Create a profit entry
     */
    public static function createProfit(Wallet $wallet, float $amount, string $description = null, string $reference = null): self
    {
        return static::create([
            'wallet_id' => $wallet->id,
            'type' => self::TYPE_PROFIT,
            'amount' => $amount,
            'description' => $description ?? "Profit from investment",
            'reference' => $reference,
            'transaction_date' => now(),
        ]);
    }

    /**
     * Create a withdrawal entry
     */
    public static function createWithdrawal(Wallet $wallet, float $amount, string $description = null, string $reference = null): self
    {
        return static::create([
            'wallet_id' => $wallet->id,
            'type' => self::TYPE_WITHDRAWAL,
            'amount' => $amount,
            'description' => $description ?? "Withdrawal",
            'reference' => $reference,
            'transaction_date' => now(),
        ]);
    }

    /**
     * Create a pool adjustment entry (for rebalancing during pool edits)
     */
    public static function createPoolAdjustment(Wallet $wallet, float $amount, string $description = null, string $reference = null): self
    {
        return static::create([
            'wallet_id' => $wallet->id,
            'type' => self::TYPE_POOL_ADJUSTMENT,
            'amount' => $amount,
            'description' => $description ?? "Pool adjustment",
            'reference' => $reference,
            'transaction_date' => now(),
        ]);
    }

    public static function getAvailableBalance(Wallet $wallet): float
    {
        $deposits = static::where('wallet_id', $wallet->id)
            ->where('type', self::TYPE_DEPOSIT)
            ->sum('amount');

        $investments = static::where('wallet_id', $wallet->id)
            ->where('type', self::TYPE_INVEST)
            ->sum('amount');

        $returns = static::where('wallet_id', $wallet->id)
            ->whereIn('type', [self::TYPE_RETURN, self::TYPE_PROFIT])
            ->sum('amount');

        $withdrawals = static::where('wallet_id', $wallet->id)
            ->where('type', self::TYPE_WITHDRAWAL)
            ->sum('amount');

        $poolAdjustments = static::where('wallet_id', $wallet->id)
            ->where('type', self::TYPE_POOL_ADJUSTMENT)
            ->sum('amount');

        return (float)($deposits + $returns + $poolAdjustments - $investments - $withdrawals);
    }
}
