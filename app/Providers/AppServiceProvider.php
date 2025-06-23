<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url("/api/user/reset-verify/" . urlencode($notifiable->email) . "/$token");

            return (new MailMessage)
                ->subject('Reset Password')
                ->line('Tap the button below to reset your password.')
                ->action('Reset Password', $url)
                ->line('If you didn`t request a password reset, no further action is required.');
        });

    }
}
