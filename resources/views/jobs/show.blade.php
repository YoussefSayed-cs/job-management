<x-jobs.layout title="Job details">
    <div class="flex flex-col gap-7">
        <x-jobs.page-heading
            :title="$job->title"
            :description="$job->company->name.' · '.$job->location"
        >
            <x-slot:action>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('jobs.edit', $job) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">Edit job</a>
                    <a href="{{ route('jobs.index') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Back to jobs</a>
                </div>
            </x-slot:action>
        </x-jobs.page-heading>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <div class="flex flex-col gap-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5 dark:border-slate-800">
                        <div>
                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $job->category->name }}</p>
                            <h2 class="mt-1 text-lg font-semibold text-slate-900 dark:text-slate-100">Job description</h2>
                        </div>
                        <x-jobs.status-badge :status="$job->status" :expired="$job->isExpired()" />
                    </div>
                    <div class="mt-5 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $job->description }}</div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Requirements</h2>
                    <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $job->requirements }}</div>
                </section>

                @if ($job->required_skills)
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Required skills</h2>
                        <div class="mt-4 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $job->required_skills }}</div>
                    </section>
                @endif
            </div>

            <aside class="flex flex-col gap-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Job information</h2>
                    <dl class="mt-4 flex flex-col divide-y divide-slate-100 dark:divide-slate-800">
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Company</dt>
                            <dd class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $job->company->name }}</dd>
                            <dd class="text-xs text-slate-500 dark:text-slate-400">{{ $job->company->industry }} · {{ $job->company->location }}</dd>
                        </div>
                        @if ($job->company->website)
                            <div class="flex flex-col gap-1 py-3">
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Website</dt>
                                <dd class="break-all text-sm text-slate-900 dark:text-slate-100">{{ $job->company->website }}</dd>
                            </div>
                        @endif
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Category</dt>
                            <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $job->category->name }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Employment type</dt>
                            <dd class="text-sm text-slate-900 dark:text-slate-100">{{ \Illuminate\Support\Str::headline($job->employment_type->value) }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Work location type</dt>
                            <dd class="text-sm text-slate-900 dark:text-slate-100">{{ \Illuminate\Support\Str::headline($job->work_location_type->value) }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Experience level</dt>
                            <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $job->experience_level ? \Illuminate\Support\Str::headline($job->experience_level->value) : 'Not specified' }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Salary range</dt>
                            <dd class="text-sm text-slate-900 dark:text-slate-100">
                                @if ($job->salary_min !== null || $job->salary_max !== null)
                                    {{ $job->salary_min !== null ? number_format((float) $job->salary_min, 2) : '—' }}
                                    –
                                    {{ $job->salary_max !== null ? number_format((float) $job->salary_max, 2) : '—' }}
                                @else
                                    Not specified
                                @endif
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Application deadline</dt>
                            <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $job->application_deadline?->format('M j, Y') ?? 'Not specified' }}</dd>
                        </div>
                        <div class="flex flex-col gap-1 py-3">
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Archived at</dt>
                            <dd class="text-sm text-slate-900 dark:text-slate-100">{{ $job->archived_at?->format('M j, Y g:i A') ?? 'Not archived' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Record details</h2>
                    <dl class="mt-4 flex flex-col gap-3">
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Created</dt>
                            <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $job->created_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Last updated</dt>
                            <dd class="mt-1 text-sm text-slate-800 dark:text-slate-200">{{ $job->updated_at->format('M j, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Job ID</dt>
                            <dd class="mt-1 break-all font-mono text-xs text-slate-600 dark:text-slate-300">{{ $job->id }}</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>
</x-jobs.layout>
