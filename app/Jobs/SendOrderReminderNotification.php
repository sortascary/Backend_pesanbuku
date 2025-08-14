<?php

namespace App\Jobs;

use App\Models\Order;
use App\Notifications\OrderReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order, public int $month) {}

    public function handle()
    {
        if ($this->order->payment !== 'angsuran') return;
        if ($this->order->status !== 'done') return;
        if ($this->order->unpaid_amount <= 0) return;

        $user = $this->order->user;
        $user->notify(new OrderReminderNotification($this->order, $this->month));
    }
}
