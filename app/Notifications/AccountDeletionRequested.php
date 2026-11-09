<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class AccountDeletionRequested extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $expiresAt = $notifiable->deletion_requested_at->copy()->addDays(30);

        $cancelUrl = URL::temporarySignedRoute(
            'account-deletion.cancel',
            $expiresAt,
            ['user' => $notifiable->getRouteKey()]
        );

        return (new MailMessage)
            ->subject('Account Deletion Request')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('We received your request to delete your account.')
            ->line('Your account is scheduled for deletion after 30 days.')
            ->action('Cancel Deletion Request', $cancelUrl)
            ->line('If you did not request this, you can ignore this email.')
            ->line('This link expires when your 30-day grace period ends.');
    }
}
