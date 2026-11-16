<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shagalni\Shared\Models\Company;

class CompanyRegistrationDecided
{
    use Dispatchable, SerializesModels;

    public function __construct(public Company $company) {}
}
