<?php

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Shagalni\Shared\Ai\AnalysisResult;
use Shagalni\Shared\Ai\DocumentParser;
use Shagalni\Shared\Ai\ResumeAnalyzer;
use Shagalni\Shared\Ai\UnsupportedResumeException;
use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Events\ApplicationSubmitted;
use Shagalni\Shared\Jobs\ProcessResumeAnalysis;
use Shagalni\Shared\Listeners\QueueResumeAnalysis;
use Shagalni\Shared\Models\AiAnalysis;
use Shagalni\Shared\Models\ResumeSnapshot;

beforeEach(function () {
    $this->owner = approvedOwner();
    $this->application = makeApplicationFor($this->owner);
    $this->snapshot = ResumeSnapshot::create([
        'application_id' => $this->application->id,
        'file_path' => 'snapshots/cv.pdf',
        'original_name' => 'cv.pdf',
    ]);
});

test('it saves the analysis result without changing application status', function () {
    app()->instance(DocumentParser::class, new class implements DocumentParser
    {
        public function extract(ResumeSnapshot $snapshot): string
        {
            return 'Experienced PHP developer';
        }
    });

    app()->instance(ResumeAnalyzer::class, new class implements ResumeAnalyzer
    {
        public function analyze(string $resumeText, array $job): AnalysisResult
        {
            return new AnalysisResult(87, 'Strong match.');
        }
    });

    (new ProcessResumeAnalysis($this->application->id))->handle(
        app(DocumentParser::class),
        app(ResumeAnalyzer::class)
    );

    $analysis = AiAnalysis::where('application_id', $this->application->id)->firstOrFail();

    expect($analysis->status)->toBe(AiAnalysisStatus::Completed)
        ->and($analysis->match_score)->toBe(87)
        ->and($analysis->feedback)->toBe('Strong match.')
        ->and($analysis->attempts)->toBe(1)
        ->and($this->application->fresh()->status)->toBe(ApplicationStatus::Pending);
});

test('it marks unsupported resumes failed without rethrowing or changing application status', function () {
    app()->instance(DocumentParser::class, new class implements DocumentParser
    {
        public function extract(ResumeSnapshot $snapshot): string
        {
            throw new UnsupportedResumeException('Unsupported resume format.');
        }
    });

    app()->instance(ResumeAnalyzer::class, new class implements ResumeAnalyzer
    {
        public function analyze(string $resumeText, array $job): AnalysisResult
        {
            throw new RuntimeException('Analyzer should not run.');
        }
    });

    (new ProcessResumeAnalysis($this->application->id))->handle(
        app(DocumentParser::class),
        app(ResumeAnalyzer::class)
    );

    $analysis = AiAnalysis::where('application_id', $this->application->id)->firstOrFail();

    expect($analysis->status)->toBe(AiAnalysisStatus::Failed)
        ->and($analysis->failure_reason)->toBe('Unsupported resume format.')
        ->and($this->application->fresh()->status)->toBe(ApplicationStatus::Pending);
});

test('it rethrows transient errors and records only a safe failure reason after final failure', function () {
    app()->instance(DocumentParser::class, new class implements DocumentParser
    {
        public function extract(ResumeSnapshot $snapshot): string
        {
            return 'Resume text';
        }
    });

    app()->instance(ResumeAnalyzer::class, new class implements ResumeAnalyzer
    {
        public function analyze(string $resumeText, array $job): AnalysisResult
        {
            throw new RuntimeException('provider secret x');
        }
    });

    $job = new ProcessResumeAnalysis($this->application->id);

    expect(fn () => $job->handle(app(DocumentParser::class), app(ResumeAnalyzer::class)))
        ->toThrow(RuntimeException::class, 'provider secret x');

    $job->failed(new RuntimeException('x'));
    $analysis = AiAnalysis::where('application_id', $this->application->id)->firstOrFail();

    expect($analysis->status)->toBe(AiAnalysisStatus::Failed)
        ->and($analysis->failure_reason)->toBe('The AI service is temporarily unavailable. You can retry the analysis later.')
        ->and($analysis->failure_reason)->not->toContain('x');
});

test('it does not process an analysis that is already completed', function () {
    $analysis = AiAnalysis::create([
        'application_id' => $this->application->id,
        'status' => AiAnalysisStatus::Completed,
        'match_score' => 42,
        'attempts' => 2,
    ]);

    app()->instance(DocumentParser::class, new class implements DocumentParser
    {
        public function extract(ResumeSnapshot $snapshot): string
        {
            throw new RuntimeException('Parser should not run.');
        }
    });

    app()->instance(ResumeAnalyzer::class, new class implements ResumeAnalyzer
    {
        public function analyze(string $resumeText, array $job): AnalysisResult
        {
            throw new RuntimeException('Analyzer should not run.');
        }
    });

    (new ProcessResumeAnalysis($this->application->id))->handle(
        app(DocumentParser::class),
        app(ResumeAnalyzer::class)
    );

    expect($analysis->fresh()->match_score)->toBe(42)
        ->and($analysis->fresh()->attempts)->toBe(2);
});

test('it returns without creating an analysis if the application was deleted', function () {
    $applicationId = $this->application->id;
    $this->application->delete();

    app()->instance(DocumentParser::class, new class implements DocumentParser
    {
        public function extract(ResumeSnapshot $snapshot): string
        {
            return '';
        }
    });

    app()->instance(ResumeAnalyzer::class, new class implements ResumeAnalyzer
    {
        public function analyze(string $resumeText, array $job): AnalysisResult
        {
            return new AnalysisResult(0, '');
        }
    });

    (new ProcessResumeAnalysis($applicationId))->handle(
        app(DocumentParser::class),
        app(ResumeAnalyzer::class)
    );

    expect(AiAnalysis::where('application_id', $applicationId)->exists())->toBeFalse();
});

test('it marks a missing job vacancy as permanently failed', function () {
    $this->application->update(['job_vacancy_id' => null]);

    app()->instance(DocumentParser::class, new class implements DocumentParser
    {
        public function extract(ResumeSnapshot $snapshot): string
        {
            return 'Resume text';
        }
    });

    app()->instance(ResumeAnalyzer::class, new class implements ResumeAnalyzer
    {
        public function analyze(string $resumeText, array $job): AnalysisResult
        {
            return new AnalysisResult(50, 'Some match.');
        }
    });

    (new ProcessResumeAnalysis($this->application->id))->handle(
        app(DocumentParser::class),
        app(ResumeAnalyzer::class)
    );

    $analysis = AiAnalysis::where('application_id', $this->application->id)->firstOrFail();

    expect($analysis->status)->toBe(AiAnalysisStatus::Failed)
        ->and($analysis->failure_reason)->toBe('The job is no longer available for analysis.');
});

test('application submission creates one pending analysis and queues the job on the ai queue', function () {
    Queue::fake();

    event(new ApplicationSubmitted($this->application));
    event(new ApplicationSubmitted($this->application));

    expect(AiAnalysis::where('application_id', $this->application->id)->count())->toBe(1)
        ->and(AiAnalysis::where('application_id', $this->application->id)->first()->status)
        ->toBe(AiAnalysisStatus::Pending);

    Queue::assertPushedOn('ai', ProcessResumeAnalysis::class);
});

test('it contains dispatcher failures in the submission listener', function () {
    Bus::shouldReceive('dispatch')->once()->andThrow(new RuntimeException('dispatcher unavailable'));

    expect(fn () => app(QueueResumeAnalysis::class)->handle(new ApplicationSubmitted($this->application)))
        ->not->toThrow(Throwable::class);

    expect(AiAnalysis::where('application_id', $this->application->id)->exists())->toBeTrue();
});

test('the analyzer receives only the explicitly allowed job context', function () {
    $receivedJob = null;

    app()->instance(DocumentParser::class, new class implements DocumentParser
    {
        public function extract(ResumeSnapshot $snapshot): string
        {
            return 'Resume text';
        }
    });

    app()->instance(ResumeAnalyzer::class, new class($receivedJob) implements ResumeAnalyzer
    {
        public function __construct(public mixed &$receivedJob) {}

        public function analyze(string $resumeText, array $job): AnalysisResult
        {
            $this->receivedJob = $job;

            return new AnalysisResult(74, 'Good fit.');
        }
    });

    (new ProcessResumeAnalysis($this->application->id))->handle(
        app(DocumentParser::class),
        app(ResumeAnalyzer::class)
    );

    expect(array_keys($receivedJob))->toBe([
        'title',
        'description',
        'requirements',
        'required_skills',
        'location',
        'employment_type',
        'work_location_type',
        'experience_level',
    ]);
});
