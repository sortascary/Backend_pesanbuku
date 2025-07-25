<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendCustomLink extends Notification
{
    use Queueable;

    protected $link;

    public function __construct($link)
    {
        $this->link = $link;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Your PDF Link')
            ->line('Click the button below to view your PDF.')
            ->action('View PDF', $this->link)
            ->line('Thank you for using our application!');
    }
}
