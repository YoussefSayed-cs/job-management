<?php

namespace App\Listeners;

use App\Events\CompanyRegistrationDecided;
use App\Notifications\CompanyRegistrationApproved;
use App\Notifications\CompanyRegistrationRejected;
use Shagalni\Shared\Enums\RegistrationStatus;
use Throwable;

class NotifyOwnerOfRegistrationDecision
{
    public function handle(CompanyRegistrationDecided $event): void
    {
        $company = $event->company;
        $owner = $company->owner;

        if ($owner === null) {
            return;
        }

        $notification = match ($company->registration_status) {
            RegistrationStatus::Approved => new CompanyRegistrationApproved($company),
            RegistrationStatus::Rejected => new CompanyRegistrationRejected($company, (string) $company->rejection_reason),
            default => null,
        };

        if ($notification === null) {
            return;
        }

        try {
            $owner->notify($notification);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
