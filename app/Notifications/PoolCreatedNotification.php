<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\DatabaseMessage;

class PoolCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $pool;

    public function __construct($pool)
    {
        $this->pool = $pool;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'title' => 'New Investment Pool',
            'message' => "A new investment pool '{$this->pool->design_name}' has been created with {$this->pool->number_of_partners} partners and requires {$this->pool->amount_required} in funding.",
            'pool_id' => $this->pool->id,
            'pool_name' => $this->pool->design_name,
            'amount_required' => $this->pool->amount_required,
            'number_of_partners' => $this->pool->number_of_partners,
            'status' => $this->pool->status,
            'action_url' => '/admin/investment-pools/' . $this->pool->id,
        ];
    }
}
