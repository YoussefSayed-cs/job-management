<x-jobs.layout title="Companies">
    <div class="space-y-6">
        <x-jobs.page-heading title="Companies"
            description="Review company registrations and browse all companies on the platform." />

        {{-- Filters --}}
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="GET" action="{{ route('companies.index') }}" class="grid gap-4 md:grid-cols-3">

                <div class="md:col-span-2">
                    <label for="search" class="mb-2 block text-sm font-medium">
                        Search companies
                    </label>
                    <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Company name, owner name or owner email..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium">
                        Registration status
                    </label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All statuses</option>

                        @foreach (\App\Enums\RegistrationStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                {{ \Illuminate\Support\Str::headline($status->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-3 md:col-span-3">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Apply Filters
                    </button>

                    <a href="{{ route('companies.index') }}"
                        class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                        Clear Filters
                    </a>
                </div>
            </form>
        </section>

        {{-- Companies Table --}}
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h2 class="font-semibold">Companies</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $companies->total() }} companies found
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead
                        class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">Owner</th>
                            <th class="px-6 py-3">Email verified</th>
                            <th class="px-6 py-3">Registration</th>
                            <th class="px-6 py-3">Submitted</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($companies as $company)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">
                                <td class="px-6 py-4">
                                    <p class="font-medium">{{ $company->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $company->industry }} · {{ $company->location }}
                                    </p>
                                </td>

                                <td class="px-6 py-4">
                                    <p>{{ $company->owner->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $company->owner->email }}</p>
                                </td>

                                <td class="px-6 py-4">
                                    {{ $company->owner->hasVerifiedEmail() ? 'Yes' : 'No' }}
                                </td>

                                <td class="px-6 py-4">
                                    <x-jobs.status-badge :status="$company->registration_status" />
                                </td>

                                <td class="px-6 py-4 text-slate-500">
                                    {{ $company->created_at->format('M j, Y') }}
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('companies.show', $company) }}"
                                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-300">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                    No companies found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                {{ $companies->links() }}
            </div>
        </section>
    </div>
</x-jobs.layout>