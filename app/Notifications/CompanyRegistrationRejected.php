<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use LogicException;
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
        $portalUrl = config('services.job_seeker_portal_url');

        if (! is_string($portalUrl) || trim($portalUrl) === '') {
            throw new LogicException('The Job Seeker Portal URL is not configured.');
        }

        return (new MailMessage)
            ->subject('Your company registration was rejected')
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('Unfortunately, the registration of "'.$this->company->name.'" was rejected.')
            ->line('Reason: '.$this->company->rejection_reason)
            ->line('You can edit your company details and resubmit your registration through the Job Seeker Portal.')
            ->action('Open Job Seeker Portal', $portalUrl);
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
