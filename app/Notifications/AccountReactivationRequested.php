<?php

namespace App\Notifications;

use App\Services\AccountLifecycleService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AccountReactivationRequested extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'account-reactivation.confirm',
            now()->addHours(AccountLifecycleService::REACTIVATION_LINK_HOURS),
            [
                'user' => $notifiable->getRouteKey(),
                'token' => $this->token,
            ]
        );

        return (new MailMessage)
            ->subject('Reactivate your account')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('We received a request to reactivate your account.')
            ->action('Reactivate my account', $url)
            ->line('This link can be used once and expires in 24 hours.')
            ->line('If you did not request this, you can ignore this email.');
    }
}
