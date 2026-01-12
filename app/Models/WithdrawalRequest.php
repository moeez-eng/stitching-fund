<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'investor_id',
        'investor_name',
        'requested_amount',
        'approved_amount',
        'status',
        'owner_notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function approve(User $user, ?float $approvedAmount = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $finalAmount = $approvedAmount ?? $this->requested_amount;
        
        // Check available balance before approval
        $availableBalance = $this->wallet->available_balance;
        Log::info('Approving withdrawal request', [
            'request_id' => $this->id,
            'requested_amount' => $this->requested_amount,
            'approved_amount' => $finalAmount,
            'available_balance' => $availableBalance,
            'wallet_id' => $this->wallet_id
        ]);
        
        if ($finalAmount > $availableBalance) {
            Log::error('Insufficient balance for withdrawal', [
                'requested' => $finalAmount,
                'available' => $availableBalance
            ]);
            return false;
        }
        
        // Create ledger entry for withdrawal
        try {
            $ledger = \App\Models\WalletLedger::createWithdrawal(
                $this->wallet,
                $finalAmount,
                "Withdrawal approved by {$user->name}",
                "WR-{$this->id}"
            );
            
            Log::info('Ledger entry created', [
                'ledger_id' => $ledger->id,
                'amount' => $finalAmount
            ]);

            $this->update([
                'status' => self::STATUS_APPROVED,
                'approved_amount' => $finalAmount,
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
            
            // Check new balance
            $newBalance = $this->wallet->refreshAvailableBalance();
            Log::info('Withdrawal approved successfully', [
                'old_balance' => $availableBalance,
                'new_balance' => $newBalance,
                'deducted' => $availableBalance - $newBalance
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to approve withdrawal request', [
                'request_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function reject(User $user, ?string $notes = null): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        return $this->update([
            'status' => self::STATUS_REJECTED,
            'owner_notes' => $notes,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }
}
