<x-jobs.layout :title="$job->title">
    <div class="space-y-6">

        <x-jobs.page-heading :title="$job->title" description="Job vacancy details and lifecycle management.">
            <x-slot:action>
                <a href="{{ route('jobs.index') }}"
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                    Back to Jobs
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold">{{ $job->title }}</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $job->company->name }} · {{ $job->location }}
                    </p>
                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium dark:bg-slate-800">
                    {{ \Illuminate\Support\Str::headline($job->status->value) }}
                </span>
            </div>

            @if ($job->isExpired())
                <p
                    class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                    This job is expired because its application deadline has passed.
                </p>
            @endif

            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs text-slate-500">Category</p>
                    <p class="mt-1 font-medium">{{ $job->category->name }}</p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Employment Type</p>
                    <p class="mt-1 font-medium">
                        {{ \Illuminate\Support\Str::headline($job->employment_type->value) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Work Location</p>
                    <p class="mt-1 font-medium">
                        {{ \Illuminate\Support\Str::headline($job->work_location_type->value) }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Experience Level</p>
                    <p class="mt-1 font-medium">
                        {{ $job->experience_level
    ? \Illuminate\Support\Str::headline($job->experience_level->value)
    : 'Not specified' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Salary Range</p>
                    <p class="mt-1 font-medium">
                        @if ($job->salary_min !== null || $job->salary_max !== null)
                            {{ $job->salary_min ?? '—' }} - {{ $job->salary_max ?? '—' }}
                        @else
                            Not specified
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Application Deadline</p>
                    <p class="mt-1 font-medium">
                        {{ $job->application_deadline?->format('M j, Y') ?? 'No deadline' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Applications</p>
                    <p class="mt-1 font-medium">{{ $job->applications_count }}</p>
                </div>

                <div>
                    <p class="text-xs text-slate-500">Created</p>
                    <p class="mt-1 font-medium">{{ $job->created_at->format('M j, Y') }}</p>
                </div>

                @if ($job->archived_at)
                    <div>
                        <p class="text-xs text-slate-500">Archived At</p>
                        <p class="mt-1 font-medium">{{ $job->archived_at->format('M j, Y H:i') }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-8 border-t border-slate-100 pt-6 dark:border-slate-800">
                <h3 class="font-semibold">Description</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">
                    {{ $job->description }}
                </p>
            </div>

            <div class="mt-6">
                <h3 class="font-semibold">Requirements</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">
                    {{ $job->requirements }}
                </p>
            </div>

            <div class="mt-6">
                <h3 class="font-semibold">Required Skills</h3>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">
                    {{ $job->required_skills ?: 'Not specified' }}
                </p>
            </div>
        </section>

        <section
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-semibold">Job Actions</h2>

            <div class="mt-4 flex flex-wrap gap-3">

                @can('update', $job)
                    <a href="{{ route('jobs.edit', $job) }}"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                        Edit
                    </a>
                @endcan

                @if ($job->status === \App\Enums\JobStatus::Active)
                    @can('close', $job)
                        <form method="POST" action="{{ route('jobs.close', $job) }}">
                            @csrf
                            <button
                                class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                                Close Job
                            </button>
                        </form>
                    @endcan
                @endif

                @if ($job->status === \App\Enums\JobStatus::Closed)
                    @can('reopen', $job)
                        <form method="POST" action="{{ route('jobs.reopen', $job) }}">
                            @csrf
                            <button
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Reopen Job
                            </button>
                        </form>
                    @endcan
                @endif

                @if ($job->status !== \App\Enums\JobStatus::Archived)
                    @can('archive', $job)
                        <form method="POST" action="{{ route('jobs.archive', $job) }}"
                            onsubmit="return confirm('Archive this job?')">
                            @csrf
                            <button
                                class="rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                                Archive Job
                            </button>
                        </form>
                    @endcan
                @endif

                @if ($job->status === \App\Enums\JobStatus::Archived)
                    @can('restore', $job)
                        <form method="POST" action="{{ route('jobs.restore', $job) }}"
                            onsubmit="return confirm('Restore this job as Active?')">
                            @csrf
                            <button
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Restore Job
                            </button>
                        </form>
                    @endcan
                @endif
            </div>

            @if ($job->status === \App\Enums\JobStatus::Archived)
                @can('delete', $job)
                    <form method="POST" action="{{ route('jobs.destroy', $job) }}"
                        class="mt-6 max-w-lg border-t border-rose-100 pt-5 dark:border-rose-900"
                        onsubmit="return confirm('This permanently deletes the job. Continue?')">
                        @csrf
                        @method('DELETE')

                        <label class="flex items-start gap-3 text-sm text-rose-700 dark:text-rose-300">
                            <input type="checkbox" name="confirmation" value="yes" required class="mt-1">
                            <span>
                                I understand that this action permanently deletes this archived job.
                            </span>
                        </label>

                        <button type="submit"
                            class="mt-4 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                            Permanently Delete
                        </button>
                    </form>
                @endcan
            @endif
        </section>

    </div>
</x-jobs.layout>