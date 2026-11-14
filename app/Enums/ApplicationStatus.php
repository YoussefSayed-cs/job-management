<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Shortlisted = 'shortlisted';
    case Interview = 'interview';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';

    // FR-94: الحالات النهائية مفيش بعدها تغيير
    public function isTerminal(): bool
    {
        return in_array($this, [
            self::Accepted,
            self::Rejected,
            self::Withdrawn,
        ]);
    }
}
