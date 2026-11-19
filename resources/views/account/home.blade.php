<x-jobs.layout title="Dashboard">
    @php
        $pendingCount = $statusDistribution[\Shagalni\Shared\Enums\ApplicationStatus::Pending->value] ?? 0;
        $activePipelineCount = ($statusDistribution[\Shagalni\Shared\Enums\ApplicationStatus::UnderReview->value] ?? 0)
            + ($statusDistribution[\Shagalni\Shared\Enums\ApplicationStatus::Shortlisted->value] ?? 0)
            + ($statusDistribution[\Shagalni\Shared\Enums\ApplicationStatus::Interview->value] ?? 0);

        $jobChips = [
            [
                'label' => __('Active'),
                'count' => $jobStats['active'],
                'url' => route('jobs.index', ['status' => 'active']),
            ],
            [
                'label' => __('Expired'),
                'count' => $jobStats['expired'],
                'url' => route('jobs.index', ['status' => 'expired']),
            ],
            [
                'label' => __('Closed'),
                'count' => $jobStats['closed'],
                'url' => route('jobs.index', ['status' => 'closed']),
            ],
            [
                'label' => __('Archived'),
                'count' => $jobStats['archived'],
                'url' => route('jobs.index', ['status' => 'archived']),
            ],
        ];
    @endphp

    <div class="flex flex-col gap-6">
        {{-- 1. Single-Line Page Header --}}
        <x-dashboard.page-header
            :title="$company->name"
            :date="false"
        >
            <x-slot:badge>
                <x-jobs.status-badge :status="$company->registration_status" />
            </x-slot:badge>

            <x-slot:meta>
                @if ($company->industry)
                    <span>{{ $company->industry }}</span>
                    <span class="text-slate-300 dark:text-slate-700">·</span>
                @endif

                @if ($company->location)
                    <span>{{ $company->location }}</span>
                    <span class="text-slate-300 dark:text-slate-700">·</span>
                @endif

                @if ($company->website)
                    <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer"
                        class="transition hover:text-indigo-600 hover:underline dark:hover:text-indigo-400">
                        {{ parse_url($company->website, PHP_URL_HOST) ?? $company->website }}
                    </a>
                    <span class="text-slate-300 dark:text-slate-700">·</span>
                @endif

                <a href="{{ route('my-company.show') }}" wire:navigate
                    class="font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                    {{ __('Manage company') }}
                </a>
            </x-slot:meta>

            <x-slot:actions>
                @can('create', \Shagalni\Shared\Models\JobVacancy::class)
                    <a href="{{ route('jobs.create') }}" wire:navigate
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                        <flux:icon.plus class="size-3.5" />
                        {{ __('Post a Job') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-dashboard.page-header>

        {{-- 2. Metric Stat Cards Grid --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <x-dashboard.stat-card
                :label="__('Total Applications')"
                :value="number_format($applicationStats['total'])"
                :subtext="__('All candidate submissions')"
                icon="document-text"
                accent="indigo"
            />

            <x-dashboard.stat-card
                :label="__('Pending Review')"
                :value="number_format($pendingCount)"
                :subtext="__('Requires candidate screening')"
                icon="clock"
                accent="amber"
                :badge="$pendingCount > 0 ? __('Action needed') : null"
            />

            <x-dashboard.stat-card
                :label="__('Active Jobs')"
                :value="number_format($jobStats['active'])"
                :subtext="__('Open for applicants')"
                icon="briefcase"
                accent="blue"
            />

            <x-dashboard.stat-card
                :label="__('Unique Applicants')"
                :value="number_format($applicationStats['applicants'])"
                :subtext="__('Distinct talent pool')"
                icon="users"
                accent="sky"
            />
        </div>

        {{-- 3. Analytics Charts Row (2 Balanced Columns) --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Jobs by Status Bar Chart --}}
            <x-dashboard.chart-card
                :title="__('Jobs by status')"
                :subtitle="__('Portfolio distribution across vacancy lifecycles.')"
            >
                <x-analytics.chart
                    type="bar"
                    label="Jobs by status"
                    :labels="$jobStatusChart['labels']"
                    :values="$jobStatusChart['values']"
                    height="h-56"
                />

                <x-slot:footer>
                    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
                        <span>
                            <span class="text-slate-500 dark:text-slate-400">{{ __('Active:') }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($jobStats['active']) }}</span>
                        </span>
                        <span class="text-slate-300 dark:text-slate-700">·</span>
                        <span>
                            <span class="text-slate-500 dark:text-slate-400">{{ __('Expired:') }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($jobStats['expired']) }}</span>
                        </span>
                        <span class="text-slate-300 dark:text-slate-700">·</span>
                        <span>
                            <span class="text-slate-500 dark:text-slate-400">{{ __('Closed:') }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($jobStats['closed']) }}</span>
                        </span>
                        <span class="text-slate-300 dark:text-slate-700">·</span>
                        <span>
                            <span class="text-slate-500 dark:text-slate-400">{{ __('Archived:') }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($jobStats['archived']) }}</span>
                        </span>
                    </div>
                </x-slot:footer>
            </x-dashboard.chart-card>

            {{-- Applications by Status Funnel --}}
            <x-dashboard.chart-card
                :title="__('Applications by status')"
                :subtitle="__('Current candidate progression across all hiring stages.')"
            >
                <x-dashboard.funnel :distribution="$statusDistribution" />

                <x-slot:footer>
                    <div class="flex items-center justify-between text-xs">
                        <span>
                            <span class="text-slate-500 dark:text-slate-400">{{ __('Total processed:') }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">{{ number_format($applicationStats['total']) }}</span>
                        </span>
                        <a href="{{ route('applications.index') }}" wire:navigate
                            class="inline-flex items-center gap-1 font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                            <span>{{ __('View pipeline') }}</span>
                            <flux:icon.arrow-right class="size-3" />
                        </a>
                    </div>
                </x-slot:footer>
            </x-dashboard.chart-card>
        </div>

        {{-- 4. Operations & Tables Row (2 Balanced Columns) --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Recent Jobs Table with Filter Chips --}}
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white">
                        {{ __('Recent Job Postings') }}
                    </h2>
                    <x-dashboard.filter-chips :chips="$jobChips" />
                </div>

                <x-dashboard.data-table
                    :headers="[__('Job Title'), __('Status'), __('Created'), ['label' => __('Action'), 'align' => 'right']]"
                    :view-all-url="auth()->user()->can('viewAny', \Shagalni\Shared\Models\JobVacancy::class) ? route('jobs.index') : null"
                    :view-all-text="__('All jobs')"
                >
                    @forelse ($recentJobs as $job)
                        <tr class="transition-colors hover:bg-slate-50/75 dark:hover:bg-slate-800/40">
                            <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">
                                @can('view', $job)
                                    <a href="{{ route('jobs.show', $job) }}" wire:navigate
                                        class="transition hover:text-indigo-600 dark:hover:text-indigo-400">
                                        {{ $job->title }}
                                    </a>
                                @else
                                    {{ $job->title }}
                                @endcan
                            </td>
                            <td class="px-5 py-3">
                                <x-jobs.status-badge :status="$job->status" />
                            </td>
                            <td class="px-5 py-3 text-slate-500 dark:text-slate-400">
                                <time datetime="{{ $job->created_at->toAtomString() }}">
                                    {{ $job->created_at->format('M j, Y') }}
                                </time>
                            </td>
                            <td class="px-5 py-3 text-right">
                                @can('view', $job)
                                    <a href="{{ route('jobs.show', $job) }}" wire:navigate
                                        class="inline-flex items-center gap-1 font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <span>{{ __('View') }}</span>
                                        <flux:icon.arrow-right class="size-3" />
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center">
                                <p class="text-xs font-medium text-slate-600 dark:text-slate-400">
                                    {{ __('No jobs yet') }}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    {{ __('Jobs you publish will appear here.') }}
                                </p>
                                @can('create', \Shagalni\Shared\Models\JobVacancy::class)
                                    <div class="mt-3">
                                        <a href="{{ route('jobs.create') }}" wire:navigate
                                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                            <flux:icon.plus class="size-3" />
                                            {{ __('Post your first job') }}
                                        </a>
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </x-dashboard.data-table>
            </div>

            {{-- Candidate Review Queue Table --}}
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white">
                        {{ __('Candidate Review Queue') }}
                    </h2>
                    @can('viewAny', \Shagalni\Shared\Models\Application::class)
                        <a href="{{ route('applications.index') }}" wire:navigate
                            class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                            {{ __('All applications') }}
                            <flux:icon.arrow-right class="size-3" />
                        </a>
                    @endcan
                </div>

                <x-dashboard.data-table
                    :headers="[__('Queue / Stage'), __('Count'), ['label' => __('Action'), 'align' => 'right']]"
                >
                    <tr class="transition-colors hover:bg-slate-50/75 dark:hover:bg-slate-800/40">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex size-7 items-center justify-center rounded-md border border-amber-200/80 bg-amber-50 text-amber-700 dark:border-amber-800/40 dark:bg-amber-950/40 dark:text-amber-300">
                                    <flux:icon.clock class="size-3.5" />
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ __('Pending Review') }}</p>
                                    <p class="text-[0.6875rem] text-slate-500 dark:text-slate-400">
                                        {{ __('New candidates awaiting initial screening') }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full border border-amber-200/80 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold tabular-nums text-amber-900 dark:border-amber-800/40 dark:bg-amber-950/40 dark:text-amber-300">
                                {{ number_format($pendingCount) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('viewAny', \Shagalni\Shared\Models\Application::class)
                                <a href="{{ route('applications.index', ['status' => 'pending']) }}" wire:navigate
                                    class="inline-flex items-center gap-1 font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <span>{{ __('Review') }}</span>
                                    <flux:icon.arrow-right class="size-3" />
                                </a>
                            @endcan
                        </td>
                    </tr>

                    <tr class="transition-colors hover:bg-slate-50/75 dark:hover:bg-slate-800/40">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex size-7 items-center justify-center rounded-md border border-indigo-200/80 bg-indigo-50 text-indigo-600 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-400">
                                    <flux:icon.user-group class="size-3.5" />
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ __('Active Pipeline') }}</p>
                                    <p class="text-[0.6875rem] text-slate-500 dark:text-slate-400">
                                        {{ __('Under review, shortlisted, and interview stages') }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full border border-indigo-200/80 bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold tabular-nums text-indigo-800 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-300">
                                {{ number_format($activePipelineCount) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('viewAny', \Shagalni\Shared\Models\Application::class)
                                <a href="{{ route('applications.index') }}" wire:navigate
                                    class="inline-flex items-center gap-1 font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <span>{{ __('Open pipeline') }}</span>
                                    <flux:icon.arrow-right class="size-3" />
                                </a>
                            @endcan
                        </td>
                    </tr>

                    <tr class="transition-colors hover:bg-slate-50/75 dark:hover:bg-slate-800/40">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="flex size-7 items-center justify-center rounded-md border border-slate-200 bg-slate-100 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    <flux:icon.document-text class="size-3.5" />
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900 dark:text-white">{{ __('All Applications') }}</p>
                                    <p class="text-[0.6875rem] text-slate-500 dark:text-slate-400">
                                        {{ __('Total historical candidate submissions') }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-100 px-2.5 py-0.5 text-xs font-semibold tabular-nums text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                {{ number_format($applicationStats['total']) }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @can('viewAny', \Shagalni\Shared\Models\Application::class)
                                <a href="{{ route('applications.index') }}" wire:navigate
                                    class="inline-flex items-center gap-1 font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                                    <span>{{ __('View all') }}</span>
                                    <flux:icon.arrow-right class="size-3" />
                                </a>
                            @endcan
                        </td>
                    </tr>
                </x-dashboard.data-table>
            </div>
        </div>
    </div>
</x-jobs.layout>
