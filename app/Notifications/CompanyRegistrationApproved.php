<?php

namespace App\Notifications;

use App\Models\Company;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyRegistrationApproved extends Notification
{
    public function __construct(public Company $company) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your company registration was approved')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('Good news! The registration of "'.$this->company->name.'" has been approved.')
            ->line('You can now sign in to the Backoffice to manage your company and publish jobs.')
            ->action('Open the Backoffice', route('dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'company_registration_approved',
            'company_id' => $this->company->id,
            'company_name' => $this->company->name,
            'message' => 'Your company registration was approved. You can now access the Backoffice.',
            'url' => route('dashboard'),
        ];
    }
}
