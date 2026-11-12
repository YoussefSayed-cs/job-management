@php
    $input = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800';

    $ownerFields = [
        ['full_name', 'Full name', 'text'],
        ['email', 'Email', 'email'],
        ['phone', 'Phone', 'text'],
    ];

    $companyFields = [
        ['company_name', 'Company name', 'text'],
        ['industry', 'Industry', 'text'],
        ['location', 'Location', 'text'],
        ['website', 'Website (optional)', 'url'],
    ];
@endphp

<x-jobs.layout title="Invite Company Owner">
    <div class="space-y-6">
        <x-jobs.page-heading title="Invite Company Owner"
            description="Creates the owner and an approved company. The owner receives an email link (valid for 48 hours) to set a password.">
            <x-slot:action>
                <a href="{{ route('users.index') }}"
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                    ← Back to users
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        <form method="POST" action="{{ route('company-owners.store') }}" class="space-y-6">
            @csrf

            <section
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-semibold">Owner account</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($ownerFields as [$name, $label, $type])
                        <div>
                            <label for="{{ $name }}" class="mb-2 block text-sm font-medium">{{ $label }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
                                value="{{ old($name) }}" required class="{{ $input }}">
                            @error($name)
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            </section>

            <section
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="font-semibold">Company</h2>

                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($companyFields as [$name, $label, $type])
                        <div>
                            <label for="{{ $name }}" class="mb-2 block text-sm font-medium">{{ $label }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
                                value="{{ old($name) }}" @required($name !== 'website') class="{{ $input }}">
                            @error($name)
                                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div class="md:col-span-2">
                        <label for="description" class="mb-2 block text-sm font-medium">Description (optional)</label>
                        <textarea id="description" name="description" rows="4" class="{{ $input }}">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <button type="submit"
                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Create and send invitation
            </button>
        </form>
    </div>
</x-jobs.layout>