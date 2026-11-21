<?php

use Shagalni\Shared\Ai\DocumentParser;
use Shagalni\Shared\Ai\ResumeAnalyzer;
use Shagalni\Shared\Jobs\ProcessResumeAnalysis;

test('the Backoffice dispatches AI analysis without running AI adapters', function () {
    // AI adapters live in the Job Seeker Portal. If you deliberately move the AI worker here, update the README and this test together.
    expect(ProcessResumeAnalysis::QUEUE)->toBe('ai')
        ->and((new ProcessResumeAnalysis('x'))->queue)->toBe('ai')
        ->and(app()->bound(DocumentParser::class))->toBeFalse()
        ->and(app()->bound(ResumeAnalyzer::class))->toBeFalse()
        ->and(file_exists(config_path('ai.php')))->toBeFalse();
});
