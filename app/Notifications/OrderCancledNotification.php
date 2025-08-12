<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class OrderCancledNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public $order, public string $message)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Notifikasi: Order Ditolak',
            'message' => 'Mohon maaf order ditolak karena ' . $this->message,
            'order_id' => $this->order->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Notifikasi: Order Ditolak',
            'message' => 'Mohon maaf order ditolak karena ' . $this->message,
            'order_id' => $this->order->id,
        ];
    }
}
