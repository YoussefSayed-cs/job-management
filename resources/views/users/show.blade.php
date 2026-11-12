@php
    use Illuminate\Support\Str;
@endphp
<x-jobs.layout :title="$user->full_name">
    <div class="space-y-6">
        <x-jobs.page-heading :title="$user->full_name" description="User account details.">
            <x-slot:action>
                <a href="{{ route('users.index') }}"
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                    ← Back to users
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        @if ($errors->has('account'))
            <div role="alert"
                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                {{ $errors->first('account') }}
            </div>
        @endif

        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Account</h2>
                <x-jobs.status-badge :status="$user->status" />
            </div>

            <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-medium">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Phone</dt>
                    <dd class="font-medium">{{ $user->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Role</dt>
                    <dd class="font-medium">{{ Str::headline($user->role->value) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Email verified</dt>
                    <dd class="font-medium">{{ $user->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Joined</dt>
                    <dd class="font-medium">{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Deletion requested</dt>
                    <dd class="font-medium">{{ $user->deletion_requested_at?->format('M j, Y') ?? '—' }}</dd>
                </div>
            </dl>

            @if ($user->company)
                <a href="{{ route('companies.show', $user->company) }}"
                    class="mt-5 inline-block text-sm font-medium text-indigo-600 hover:underline">
                    View company: {{ $user->company->name }} →
                </a>
            @endif
        </section>

        @can('resendInvitation', $user)
            <section
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-semibold">Invitation</h2>
                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
                    This owner has not set a password yet. If the link expired or got lost, send a new one
                    (valid for 48 hours). Older links stop working.
                </p>

                <form method="POST" action="{{ route('users.resend-invitation', $user) }}" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Resend invitation
                    </button>
                </form>
            </section>
        @endcan
        {{-- Account actions --}}
        @canany(['suspend', 'deactivate', 'reactivate'], $user)
            @php
                $status = $user->status;
                $isActive = $status === \App\Enums\AccountStatus::Active;
                $isSuspended = $status === \App\Enums\AccountStatus::Suspended;
                $isDeactivated = $status === \App\Enums\AccountStatus::Deactivated;
            @endphp

            <section
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-semibold">Account actions</h2>

                @if ($user->deletion_requested_at)
                    <p
                        class="mt-3 rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-950 dark:text-amber-200">
                        This user requested account deletion. Changing the status here does not cancel the request;
                        access stays blocked until the user cancels it.
                    </p>
                @endif

                <div class="mt-4 flex flex-wrap gap-3">
                    @if ($isActive || $isDeactivated)
                        <form method="POST" action="{{ route('users.suspend', $user) }}"
                            onsubmit="return confirm('Suspend this account? The user will not be able to sign in.');">
                            @csrf
                            <button type="submit"
                                class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700">
                                Suspend
                            </button>
                        </form>
                    @endif

                    @if ($isActive)
                        <form method="POST" action="{{ route('users.deactivate', $user) }}"
                            onsubmit="return confirm('Deactivate this account?');">
                            @csrf
                            <button type="submit"
                                class="rounded-lg bg-slate-600 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                                Deactivate
                            </button>
                        </form>
                    @endif

                    @if ($isSuspended || $isDeactivated)
                        <form method="POST" action="{{ route('users.reactivate', $user) }}"
                            onsubmit="return confirm('Reactivate this account?');">
                            @csrf
                            <button type="submit"
                                class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                Reactivate
                            </button>
                        </form>
                    @endif
                </div>

                @if ($user->company)
                    <p class="mt-4 text-sm text-slate-500">
                        This user owns a company. While the account is suspended or deactivated, the company is
                        treated as inactive and its jobs are hidden.
                    </p>
                @endif
            </section>
        @endcanany
    </div>
</x-jobs.layout>