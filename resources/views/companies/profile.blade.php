<x-jobs.layout title="My Company">
    <div class="space-y-6">

        <x-jobs.page-heading title="My Company" description="Manage your company profile information." />

        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            {{-- Approved Company Information --}}
            <div class="mb-6 border-b border-slate-200 pb-5 dark:border-slate-700">
                <h2 class="text-lg font-semibold">Approved Information</h2>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-slate-500">Company Name</p>
                        <p class="font-medium">{{ $company->name }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">Industry</p>
                        <p class="font-medium">{{ $company->industry }}</p>
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-500">
                    Company Name and Industry changes require Admin approval.
                </p>
            </div>

            {{-- Editable Information --}}
            <form method="POST" action="{{ route('my-company.update') }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <label for="description" class="mb-2 block text-sm font-medium">
                        Company Description
                    </label>

                    <textarea id="description" name="description" rows="5" maxlength="5000"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('description', $company->description) }}</textarea>

                    @error('description')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="location" class="mb-2 block text-sm font-medium">
                        Location
                    </label>

                    <input type="text" id="location" name="location" value="{{ old('location', $company->location) }}"
                        required maxlength="255"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">

                    @error('location')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="website" class="mb-2 block text-sm font-medium">
                        Website
                    </label>

                    <input type="url" id="website" name="website" value="{{ old('website', $company->website) }}"
                        placeholder="https://example.com" maxlength="255"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">

                    @error('website')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="logo" class="mb-2 block text-sm font-medium">
                        Company Logo
                    </label>

                    @if ($company->logo)
                        <div class="mb-3">
                            <img src="{{ asset('storage/' . $company->logo) }}"
                                alt="Company Logo" class="h-24 w-24 rounded-xl border border-slate-200 object-cover">
                        </div>
                    @endif

                    <input type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png,.webp"
                        class="block w-full text-sm">

                    <p class="mt-1 text-xs text-slate-500">
                        JPG, JPEG, PNG or WEBP. Maximum size: 2 MB.
                    </p>

                    @error('logo')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end border-t border-slate-200 pt-5 dark:border-slate-700">
                    <button type="submit"
                        class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Save Changes
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-jobs.layout>