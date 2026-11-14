<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ApplicationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public string $applicationId;

    public string $jobTitle;

    public string $companyName;

    public string $status;

    public ?string $message;

    public function __construct(Application $application, ?string $message = null)
    {
        $this->applicationId = $application->id;
        $this->jobTitle = $application->job_title;
        $this->companyName = $application->company_name;
        $this->status = $application->status->value;
        $this->message = $message;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Update on your application for '.$this->jobTitle)
            ->greeting('Hello '.$notifiable->full_name.',')
            ->line('Your application for "'.$this->jobTitle.'" at '.$this->companyName.' is now: '.$this->statusLabel().'.');

        if ($this->message) {
            $mail->line('Message from the company:')
                ->line($this->message);
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'application_status_changed',
            'application_id' => $this->applicationId,
            'job_title' => $this->jobTitle,
            'company_name' => $this->companyName,
            'status' => $this->status,
            'message' => 'Your application for "'.$this->jobTitle.'" is now '.$this->statusLabel().'.',
            'status_message' => $this->message,
        ];
    }

    private function statusLabel(): string
    {
        return Str::headline($this->status);
    }
}
