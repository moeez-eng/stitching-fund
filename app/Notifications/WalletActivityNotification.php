<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class WalletActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $walletLedger;

    public function __construct($walletLedger)
    {
        $this->walletLedger = $walletLedger;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $typeMessages = [
            'deposit' => 'Funds deposited to your wallet',
            'invest' => 'Funds invested from your wallet',
            'return' => 'Investment return credited to your wallet',
            'profit' => 'Profit credited to your wallet',
            'withdrawal' => 'Funds withdrawn from your wallet',
            'pool_adjustment' => 'Pool adjustment processed',
        ];

        $message = $typeMessages[$this->walletLedger->type] ?? 'Wallet activity occurred';

        return [
            'title' => 'Wallet Activity',
            'message' => "{$message}: {$this->walletLedger->description} (Amount: {$this->walletLedger->amount})",
            'wallet_ledger_id' => $this->walletLedger->id,
            'type' => $this->walletLedger->type,
            'amount' => $this->walletLedger->amount,
            'description' => $this->walletLedger->description,
            'transaction_date' => $this->walletLedger->transaction_date,
            'action_url' => '/admin/wallets/' . $this->walletLedger->wallet_id,
        ];
    }
}
