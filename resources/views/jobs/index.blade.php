<x-jobs.layout title="Job Management">
    <div class="space-y-6">

        <x-jobs.page-heading title="Job Management" description="Browse, manage and maintain job vacancies.">
            <x-slot:action>
                @can('create', \App\Models\JobVacancy::class)
                    <a href="{{ route('jobs.create') }}"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Create Job
                    </a>
                @endcan
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
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="GET" action="{{ route('jobs.index') }}" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">

                <div>
                    <label for="search" class="mb-2 block text-sm font-medium">Search</label>
                    <input id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Job title, location or company..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium">Status</label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All statuses</option>
                        @foreach (\App\Enums\JobStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                {{ \Illuminate\Support\Str::headline($status->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="job_category_id" class="mb-2 block text-sm font-medium">Category</label>
                    <select id="job_category_id" name="job_category_id"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['job_category_id'] ?? '') === $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="employment_type" class="mb-2 block text-sm font-medium">Employment Type</label>
                    <select id="employment_type" name="employment_type"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All types</option>
                        @foreach (\App\Enums\EmploymentType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(($filters['employment_type'] ?? '') === $type->value)>
                                {{ \Illuminate\Support\Str::headline($type->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="work_location_type" class="mb-2 block text-sm font-medium">Work Location</label>
                    <select id="work_location_type" name="work_location_type"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All locations</option>
                        @foreach (\App\Enums\WorkLocationType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(($filters['work_location_type'] ?? '') === $type->value)>
                                {{ \Illuminate\Support\Str::headline($type->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="experience_level" class="mb-2 block text-sm font-medium">Experience Level</label>
                    <select id="experience_level" name="experience_level"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All levels</option>
                        @foreach (\App\Enums\ExperienceLevel::cases() as $level)
                            <option value="{{ $level->value }}" @selected(($filters['experience_level'] ?? '') === $level->value)>
                                {{ \Illuminate\Support\Str::headline($level->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-3 md:col-span-2 lg:col-span-3">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Apply Filters
                    </button>

                    <a href="{{ route('jobs.index') }}"
                        class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                        Clear Filters
                    </a>
                </div>
            </form>
        </section>

        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h2 class="font-semibold">Jobs</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $jobs->total() }} jobs found
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[850px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800">
                        <tr>
                            <th class="px-5 py-3">Job</th>
                            @if (auth()->user()->role === \App\Enums\UserRole::Admin)
                                <th class="px-5 py-3">Company</th>
                            @endif
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">Employment</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Deadline</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($jobs as $job)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-5 py-4">
                                    <p class="font-semibold">{{ $job->title }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $job->location }}</p>
                                </td>

                                @if (auth()->user()->role === \App\Enums\UserRole::Admin)
                                    <td class="px-5 py-4">{{ $job->company->name }}</td>
                                @endif

                                <td class="px-5 py-4">{{ $job->category->name }}</td>

                                <td class="px-5 py-4">
                                    {{ \Illuminate\Support\Str::headline($job->employment_type->value) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium dark:bg-slate-800">
                                        {{ \Illuminate\Support\Str::headline($job->status->value) }}
                                    </span>
                                    @if ($job->isExpired())
                                        <span class="mt-1 block text-xs text-amber-600">Expired</span>
                                    @endif
                                </td>

                                <td class="px-5 py-4 text-slate-500">
                                    {{ $job->application_deadline?->format('M j, Y') ?? 'No deadline' }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('jobs.show', $job) }}"
                                        class="font-semibold text-indigo-600 hover:text-indigo-800">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                                    No jobs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                {{ $jobs->links() }}
            </div>
        </section>
    </div>
</x-jobs.layout>