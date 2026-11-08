<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case CompanyOwner = 'company_owner';
    case JobSeeker = 'job_seeker';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::CompanyOwner => 'Company Owner',
            self::JobSeeker => 'Job Seeker',
        };
    }
}