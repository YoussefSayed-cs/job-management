<?php

namespace App\Notifications;

use App\Services\CompanyOwnerInvitationService;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class CompanyOwnerInvitation extends Notification
{
    public function __construct(public string $token) {}

    public function via(object $notifiable): array
    {
        // Mail only: the owner cannot use the app yet, so an in-app notification is useless.
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = URL::temporarySignedRoute(
            'company-owner-invitation.show',
            now()->addHours(CompanyOwnerInvitationService::INVITATION_LINK_HOURS),
            [
                'user' => $notifiable->getRouteKey(),
                'token' => $this->token,
            ]
        );

        return (new MailMessage)
            ->subject('You have been invited to Shagalni')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('An administrator created a company owner account for you.')
            ->line('Set your password to activate the account and open the Backoffice.')
            ->action('Set my password', $url)
            ->line('This link can be used once and expires in 48 hours.')
            ->line('If you were not expecting this invitation, you can ignore this email.');
    }
}
