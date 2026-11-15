<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Shagalni\Shared\Models\Company;

class CompanyRegistrationRejected extends Notification
{
    public function __construct(public Company $company) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your company registration was rejected')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('Unfortunately, the registration of "'.$this->company->name.'" was rejected.')
            ->line('Reason: '.$this->company->rejection_reason)
            ->line('You can sign in to the Backoffice, edit your company details and resubmit your registration.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'company_registration_rejected',
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'message' => 'Your company registration was rejected.',
            'rejection_reason' => $this->company->rejection_reason,
        ];
    }
}
