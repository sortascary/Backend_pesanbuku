<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public $order, public int $month)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Reminder: Ingat pembayaran Angsuran!',
            'message' => 'Mohon untuk pembayaran Angsuran untuk bulan ke-' . $this->month,
            'order_id' => $this->order->id,
            'month' => $this->month,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reminder: Ingat pembayaran Angsuran!',
            'message' => 'Mohon untuk pembayaran Angsuran untuk bulan ke-' . $this->month,
            'order_id' => $this->order->id,
            'month' => $this->month,
        ];
    }
}
