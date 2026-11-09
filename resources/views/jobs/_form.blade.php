@php
    $selectedCompanyId = old('company_id', $job?->company_id ?? auth()->user()->company?->id);
    $selectedCategoryId = old('job_category_id', $job?->job_category_id);
@endphp

<section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7 dark:border-slate-800 dark:bg-slate-900">
    <div class="grid gap-5 md:grid-cols-2">
        @if (auth()->user()->role === \App\Enums\UserRole::Admin)
            <div>
                <label for="company_id" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Company <span class="text-rose-600">*</span></label>
                <select id="company_id" name="company_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                    <option value="">Select a company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected((string) $selectedCompanyId === (string) $company->id)>{{ $company->name }}</option>
                    @endforeach
                </select>
                @error('company_id')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
            </div>
        @else
            <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
        @endif

        <div>
            <label for="job_category_id" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Category <span class="text-rose-600">*</span></label>
            <select id="job_category_id" name="job_category_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('job_category_id')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="title" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Job title <span class="text-rose-600">*</span></label>
            <input id="title" name="title" value="{{ old('title', $job?->title) }}" required maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500">
            @error('title')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="location" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Location <span class="text-rose-600">*</span></label>
            <input id="location" name="location" value="{{ old('location', $job?->location) }}" required maxlength="255" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500">
            @error('location')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Status <span class="text-rose-600">*</span></label>
            <select id="status" name="status" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $job?->status?->value ?? \App\Enums\JobStatus::Active->value) === $status->value)>{{ \Illuminate\Support\Str::headline($status->value) }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="employment_type" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Employment type <span class="text-rose-600">*</span></label>
            <select id="employment_type" name="employment_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                @foreach ($employmentTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('employment_type', $job?->employment_type?->value) === $type->value)>{{ \Illuminate\Support\Str::headline($type->value) }}</option>
                @endforeach
            </select>
            @error('employment_type')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="work_location_type" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Work location type <span class="text-rose-600">*</span></label>
            <select id="work_location_type" name="work_location_type" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                @foreach ($workLocationTypes as $type)
                    <option value="{{ $type->value }}" @selected(old('work_location_type', $job?->work_location_type?->value) === $type->value)>{{ \Illuminate\Support\Str::headline($type->value) }}</option>
                @endforeach
            </select>
            @error('work_location_type')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="experience_level" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Experience level</label>
            <select id="experience_level" name="experience_level" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                <option value="">Not specified</option>
                @foreach ($experienceLevels as $level)
                    <option value="{{ $level->value }}" @selected(old('experience_level', $job?->experience_level?->value) === $level->value)>{{ \Illuminate\Support\Str::headline($level->value) }}</option>
                @endforeach
            </select>
            @error('experience_level')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="salary_min" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Minimum salary</label>
            <input id="salary_min" name="salary_min" type="number" min="0" step="0.01" value="{{ old('salary_min', $job?->salary_min) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            @error('salary_min')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="salary_max" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Maximum salary</label>
            <input id="salary_max" name="salary_max" type="number" min="0" step="0.01" value="{{ old('salary_max', $job?->salary_max) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            @error('salary_max')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="application_deadline" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Application deadline</label>
            <input id="application_deadline" name="application_deadline" type="date" value="{{ old('application_deadline', $job?->application_deadline?->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            @error('application_deadline')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="archived_at" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Archived at</label>
            <input id="archived_at" name="archived_at" type="datetime-local" value="{{ old('archived_at', $job?->archived_at?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
            @error('archived_at')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="description" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Description <span class="text-rose-600">*</span></label>
            <textarea id="description" name="description" rows="5" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500">{{ old('description', $job?->description) }}</textarea>
            @error('description')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="requirements" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Requirements <span class="text-rose-600">*</span></label>
            <textarea id="requirements" name="requirements" rows="5" required class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500">{{ old('requirements', $job?->requirements) }}</textarea>
            @error('requirements')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>

        <div class="md:col-span-2">
            <label for="required_skills" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Required skills</label>
            <textarea id="required_skills" name="required_skills" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500">{{ old('required_skills', $job?->required_skills) }}</textarea>
            @error('required_skills')<p class="mt-1.5 text-sm text-rose-700">{{ $message }}</p>@enderror
        </div>
    </div>
</section>
