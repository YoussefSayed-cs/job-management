<?php

use Shagalni\Shared\Enums\ApplicationStatus;

test('application statuses expose their sequential next status', function () {
    $nextStatuses = [
        ApplicationStatus::Pending->value => ApplicationStatus::UnderReview,
        ApplicationStatus::UnderReview->value => ApplicationStatus::Shortlisted,
        ApplicationStatus::Shortlisted->value => ApplicationStatus::Interview,
        ApplicationStatus::Interview->value => ApplicationStatus::Accepted,
    ];

    foreach (ApplicationStatus::cases() as $status) {
        expect($status->next())->toBe($nextStatuses[$status->value] ?? null);
    }
});

test('application statuses allow only sequential progression or rejection', function () {
    $allowedTransitions = [
        ApplicationStatus::Pending->value => [
            ApplicationStatus::UnderReview->value,
            ApplicationStatus::Rejected->value,
        ],
        ApplicationStatus::UnderReview->value => [
            ApplicationStatus::Shortlisted->value,
            ApplicationStatus::Rejected->value,
        ],
        ApplicationStatus::Shortlisted->value => [
            ApplicationStatus::Interview->value,
            ApplicationStatus::Rejected->value,
        ],
        ApplicationStatus::Interview->value => [
            ApplicationStatus::Accepted->value,
            ApplicationStatus::Rejected->value,
        ],
    ];

    foreach (ApplicationStatus::cases() as $current) {
        foreach (ApplicationStatus::cases() as $target) {
            $expected = in_array(
                $target->value,
                $allowedTransitions[$current->value] ?? [],
                true
            );

            expect($current->canTransitionTo($target))->toBe($expected);
        }
    }
});
