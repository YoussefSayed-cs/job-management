<x-jobs.layout title="Dashboard">
    @php
        $maxCompanyApps = !empty($topCompanyValues) ? max($topCompanyValues) : 1;
        $hasAttentionCounts = isset($activity['pending_approval'], $activity['pending_change_requests'])
            && is_numeric($activity['pending_approval'])
            && is_numeric($activity['pending_change_requests']);
        $pendingCompanyApprovals = $hasAttentionCounts ? (int) $activity['pending_approval'] : null;
        $pendingChangeRequests = $hasAttentionCounts ? (int) $activity['pending_change_requests'] : null;
        $pendingAttentionCount = ($pendingCompanyApprovals ?? 0) + ($pendingChangeRequests ?? 0);
    @endphp

    <div class="flex flex-col gap-6">
        {{-- 1. Page Header --}}
        <x-dashboard.page-header :title="__('Platform overview')">
            <x-slot:actions>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    {{ __('Last 30 days') }}
                </span>
            </x-slot:actions>
        </x-dashboard.page-header>

        {{-- 2. Key metrics --}}
        <x-dashboard.kpi-strip :stats="[
            [
                'label' => __('Total Users'),
                'value' => number_format($totals['users']),
                'subtext' => '+' . number_format($activity['new_users']) . ' ' . __('this month'),
            ],
            [
                'label' => __('Companies'),
                'value' => number_format($totals['companies']),
                'subtext' => '+' . number_format($activity['company_registrations']) . ' ' . __('this month'),
            ],
            [
                'label' => __('Published Jobs'),
                'value' => number_format($totals['jobs']),
                'subtext' => '+' . number_format($activity['jobs_published']) . ' ' . __('this month'),
            ],
            [
                'label' => __('Total Applications'),
                'value' => number_format($totals['applications']),
                'subtext' => '+' . number_format($activity['applications_submitted']) . ' ' . __('this month'),
            ],
        ]" />

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
                :subtitle="__('Pending company approvals and change requests.')"
                :headers="[__('Queue / Entity'), __('Status'), ['label' => __('Action'), 'align' => 'right']]"
            >
                @if ($hasAttentionCounts && $pendingAttentionCount === 0)
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-xs text-slate-500 dark:text-slate-400">
                            {{ __('Nothing needs your attention') }}
                        </td>
                    </tr>
                @else
                    @if (!$hasAttentionCounts || $pendingCompanyApprovals > 0)
                        <tr class="transition-colors hover:bg-slate-50/75 dark:hover:bg-slate-800/40">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2.5">
                            <flux:icon.building-office-2 class="size-4 shrink-0 text-slate-400" />
                            <div>
                                <p class="font-medium text-slate-900 dark:text-white">{{ __('Company Registrations') }}</p>
                                <p class="text-[0.6875rem] text-slate-500 dark:text-slate-400">
                                    {{ __('Pending approval') }}
                                </p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs font-medium tabular-nums text-slate-600 dark:text-slate-300">
                            {{ $hasAttentionCounts ? __(':count pending', ['count' => number_format($pendingCompanyApprovals)]) : __('Pending count unavailable') }}
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
                    @endif

                    @if (!$hasAttentionCounts || $pendingChangeRequests > 0)
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
                        <span class="text-xs font-medium tabular-nums text-slate-600 dark:text-slate-300">
                            {{ $hasAttentionCounts ? __(':count pending', ['count' => number_format($pendingChangeRequests)]) : __('Pending count unavailable') }}
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
                    @endif
                @endif
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
