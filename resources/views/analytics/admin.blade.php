<x-jobs.layout title="Dashboard">
    @php
        $maxCompanyApps = !empty($topCompanyValues) ? max($topCompanyValues) : 1;
    @endphp

    <div class="flex flex-col gap-6">
        {{-- 1. Page Header --}}
        <x-dashboard.page-header :title="__('Platform Dashboard')">
            <x-slot:actions>
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-600 shadow-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300">
                    <flux:icon.calendar class="me-1.5 size-3.5 text-slate-400 dark:text-slate-500" />
                    {{ __('Last 30 days') }}
                </span>
            </x-slot:actions>
        </x-dashboard.page-header>

        {{-- 2. Metric Stat Cards Grid --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <x-dashboard.stat-card
                :label="__('Total Users')"
                :value="number_format($totals['users'])"
                :subtext="'+' . number_format($activity['new_users']) . ' ' . __('this month')"
                icon="users"
                accent="indigo"
            />

            <x-dashboard.stat-card
                :label="__('Companies')"
                :value="number_format($totals['companies'])"
                :subtext="'+' . number_format($activity['company_registrations']) . ' ' . __('this month')"
                icon="building-office-2"
                accent="blue"
            />

            <x-dashboard.stat-card
                :label="__('Published Jobs')"
                :value="number_format($totals['jobs'])"
                :subtext="'+' . number_format($activity['jobs_published']) . ' ' . __('this month')"
                icon="briefcase"
                accent="sky"
            />

            <x-dashboard.stat-card
                :label="__('Total Applications')"
                :value="number_format($totals['applications'])"
                :subtext="'+' . number_format($activity['applications_submitted']) . ' ' . __('this month')"
                icon="document-text"
                accent="indigo"
            />
        </div>

        {{-- 3. Analytics Charts Row --}}
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Applications per day (2/3 width) --}}
            <x-dashboard.chart-card
                class="lg:col-span-2"
                :title="__('Applications per day')"
                :subtitle="__('Candidate applications submitted over the rolling 30-day window.')"
            >
                <x-analytics.chart
                    type="line"
                    label="Applications submitted"
                    :labels="$dailyLabels"
                    :values="$dailyValues"
                    height="h-64"
                />
            </x-dashboard.chart-card>

            {{-- Application Funnel (1/3 width) --}}
            <x-dashboard.chart-card
                :title="__('Application funnel')"
                :subtitle="__('Current pipeline status distribution.')"
            >
                <x-dashboard.funnel :distribution="$distribution" />

                <x-slot:footer>
                    <div class="flex items-center justify-between text-xs">
                        <span>
                            <span class="text-slate-500 dark:text-slate-400">{{ __('Acceptance:') }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ $totals['acceptance_rate'] === null ? '—' : $totals['acceptance_rate'] . '%' }}
                            </span>
                        </span>
                        <span class="text-slate-300 dark:text-slate-700">·</span>
                        <span>
                            <span class="text-slate-500 dark:text-slate-400">{{ __('Rejection:') }}</span>
                            <span class="font-semibold tabular-nums text-slate-900 dark:text-white">
                                {{ $totals['rejection_rate'] === null ? '—' : $totals['rejection_rate'] . '%' }}
                            </span>
                        </span>
                    </div>
                </x-slot:footer>
            </x-dashboard.chart-card>
        </div>

        {{-- 4. Operations & Tables Row --}}
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Needs attention data-table --}}
            <x-dashboard.data-table
                :title="__('Needs attention')"
                :subtitle="__('Pending platform governance reviews and onboarding requests.')"
                :headers="[__('Queue / Entity'), __('Status'), ['label' => __('Action'), 'align' => 'right']]"
            >
                <tr class="transition-colors hover:bg-slate-50/75 dark:hover:bg-slate-800/40">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <flux:icon.building-office-2 class="size-4 shrink-0 text-slate-400" />
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ __('Company Registrations') }}</p>
                                <p class="text-[0.6875rem] text-slate-500 dark:text-slate-400">
                                    {{ __('+ :count new registrations submitted this month', ['count' => number_format($activity['company_registrations'])]) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center rounded-full border border-amber-200/80 bg-amber-50 px-2.5 py-0.5 text-xs font-semibold capitalize text-amber-900 dark:border-amber-800/40 dark:bg-amber-950/40 dark:text-amber-300">
                            {{ __('Pending') }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        @can('viewAny', \Shagalni\Shared\Models\Company::class)
                            <a href="{{ route('companies.index', ['status' => 'pending']) }}" wire:navigate
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
                            <flux:icon.clipboard-document-check class="size-4 shrink-0 text-slate-400" />
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ __('Company Change Requests') }}</p>
                                <p class="text-[0.6875rem] text-slate-500 dark:text-slate-400">
                                    {{ __('Pending profile and corporate info changes') }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center rounded-full border border-sky-200/80 bg-sky-50 px-2.5 py-0.5 text-xs font-semibold capitalize text-sky-800 dark:border-sky-800/40 dark:bg-sky-950/40 dark:text-sky-300">
                            {{ __('Under Review') }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        @can('viewAny', \Shagalni\Shared\Models\CompanyChangeRequest::class)
                            <a href="{{ route('company-change-requests.index') }}" wire:navigate
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
                            <flux:icon.arrow-path class="size-4 shrink-0 text-slate-400" />
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ __('Pipeline Status Transitions') }}</p>
                                <p class="text-[0.6875rem] text-slate-500 dark:text-slate-400">
                                    {{ __(':count pipeline status changes recorded this month', ['count' => number_format($activity['status_changes'])]) }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="inline-flex items-center rounded-full border border-indigo-200/80 bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold capitalize text-indigo-700 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-300">
                            {{ __('Active') }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-right">
                        <span class="text-slate-400 dark:text-slate-500">—</span>
                    </td>
                </tr>
            </x-dashboard.data-table>

            {{-- Top companies data-table with inline progress bars --}}
            <x-dashboard.data-table
                :title="__('Top companies')"
                :subtitle="__('Employers with the highest application volume.')"
                :headers="[__('Company'), ['label' => __('Applications'), 'align' => 'right']]"
                :view-all-url="auth()->user()->can('viewAny', \Shagalni\Shared\Models\Company::class) ? route('companies.index') : null"
                :view-all-text="__('All companies')"
            >
                @forelse ($topCompanyNames as $i => $name)
                    @php
                        $apps = $topCompanyValues[$i] ?? 0;
                        $pct = $maxCompanyApps > 0 ? ($apps / $maxCompanyApps) * 100 : 0;
                    @endphp
                    <tr class="transition-colors hover:bg-slate-50/75 dark:hover:bg-slate-800/40">
                        <td class="px-5 py-3 font-medium text-slate-900 dark:text-white">
                            <div class="flex items-center gap-2">
                                <span class="flex size-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    {{ $i + 1 }}
                                </span>
                                <span>{{ $name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-3">
                                <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800" title="{{ number_format($apps) }} applications">
                                    <div class="h-full rounded-full bg-indigo-600 dark:bg-indigo-500" style="width: {{ max($pct, 4) }}%"></div>
                                </div>
                                <span class="min-w-8 text-right font-medium tabular-nums text-slate-900 dark:text-white">
                                    {{ number_format($apps) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="px-5 py-6 text-center text-slate-400">
                            {{ __('No company data recorded yet.') }}
                        </td>
                    </tr>
                @endforelse
            </x-dashboard.data-table>
        </div>
    </div>
</x-jobs.layout>
