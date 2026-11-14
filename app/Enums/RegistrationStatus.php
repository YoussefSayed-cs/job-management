<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
