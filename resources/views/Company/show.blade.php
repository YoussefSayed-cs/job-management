@php
    $owner = $company->owner;
    $ownerVerified = $owner->hasVerifiedEmail();
    $isPending = $company->registration_status === \App\Enums\RegistrationStatus::PendingApproval;
@endphp

<x-jobs.layout :title="$company->name">
    <div class="space-y-6">
        <x-jobs.page-heading :title="$company->name" description="Company details and registration review.">
            <x-slot:action>
                <a href="{{ route('companies.index') }}"
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                    ← Back to companies
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        @if ($errors->has('company'))
            <div role="alert"
                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                {{ $errors->first('company') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Company --}}
            <section
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <h2 class="font-semibold">Company</h2>
                    <x-jobs.status-badge :status="$company->registration_status" />
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500">Industry</dt>
                        <dd class="font-medium">{{ $company->industry }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Location</dt>
                        <dd class="font-medium">{{ $company->location }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Website</dt>
                        <dd class="font-medium">{{ $company->website ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Description</dt>
                        <dd class="font-medium whitespace-pre-line">{{ $company->description ?? '—' }}</dd>
                    </div>
                </dl>
            </section>

            {{-- Owner --}}
            <section
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-semibold">Owner</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500">Full name</dt>
                        <dd class="font-medium">{{ $owner->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email</dt>
                        <dd class="font-medium">{{ $owner->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Phone</dt>
                        <dd class="font-medium">{{ $owner->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Email verified</dt>
                        <dd class="font-medium">{{ $ownerVerified ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Account status</dt>
                        <dd class="font-medium"><x-jobs.status-badge :status="$owner->status" /></dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Review --}}
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="font-semibold">Registration review</h2>

            @if ($isPending)
                @if (!$ownerVerified)
                    <p
                        class="mt-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                        This registration cannot be reviewed yet. The owner has not verified their email address.
                    </p>
                @else
                    <div class="mt-4 grid gap-6 md:grid-cols-2">
                        <form method="POST" action="{{ route('companies.approve', $company) }}"
                            onsubmit="return confirm('Approve this company registration?');">
                            @csrf

                            <p class="text-sm text-slate-600 dark:text-slate-300">
                                Approving activates the owner's Backoffice access and sends them an email and an in-app
                                notification.
                            </p>

                            <button type="submit"
                                class="mt-4 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Approve registration
                            </button>
                        </form>

                        <form method="POST" action="{{ route('companies.reject', $company) }}">
                            @csrf

                            <label for="rejection_reason" class="mb-2 block text-sm font-medium">
                                Rejection reason
                            </label>
                            <textarea id="rejection_reason" name="rejection_reason" rows="4" required
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                                placeholder="Explain what the owner needs to fix (min. 10 characters)">{{ old('rejection_reason') }}</textarea>

                            @error('rejection_reason')
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror

                            <button type="submit"
                                class="mt-4 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                                Reject registration
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <dl class="mt-4 space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500">Reviewed by</dt>
                        <dd class="font-medium">{{ $company->reviewer?->full_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500">Reviewed at</dt>
                        <dd class="font-medium">{{ $company->reviewed_at?->format('M j, Y H:i') ?? '—' }}</dd>
                    </div>
                    @if ($company->rejection_reason)
                        <div>
                            <dt class="text-slate-500">Rejection reason</dt>
                            <dd class="font-medium whitespace-pre-line">{{ $company->rejection_reason }}</dd>
                        </div>
                    @endif
                </dl>
            @endif
        </section>

        {{-- Jobs --}}
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h2 class="font-semibold">Latest jobs</h2>
            </div>

            <ul class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($jobs as $job)
                    <li class="flex items-center justify-between px-5 py-3 text-sm">
                        <span class="font-medium">{{ $job->title }}</span>
                        <x-jobs.status-badge :status="$job->status" />
                    </li>
                @empty
                    <li class="px-5 py-8 text-center text-sm text-slate-500">
                        This company has no jobs yet.
                    </li>
                @endforelse
            </ul>
        </section>
    </div>
</x-jobs.layout>