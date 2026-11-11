<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Services\AccountLifecycleService;
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
        $requestedAt = $notifiable->deletion_requested_at;

        $expiresAt = $requestedAt->copy()->addDays(AccountLifecycleService::DELETION_GRACE_DAYS);

        $cancelUrl = URL::temporarySignedRoute(
            'account-deletion.cancel',
            $expiresAt,
            [
                'user' => $notifiable->getRouteKey(),
                'requested' => $requestedAt->getTimestamp(),
            ]
        );

        return (new MailMessage)
            ->subject('Account Deletion Request')
            ->greeting('Hello ' . $notifiable->full_name . ',')
            ->line('We received your request to delete your account.')
            ->line('Your account is scheduled for deletion after ' . AccountLifecycleService::DELETION_GRACE_DAYS . ' days.')
            ->action('Cancel Deletion Request', $cancelUrl)
            ->line('This link can be used once and expires when your 30-day grace period ends.')
            ->line('If you did not request this, you can ignore this email.');
    }
}
