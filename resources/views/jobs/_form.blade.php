<div class="space-y-5">

    <div>
        <label for="title" class="mb-2 block text-sm font-medium">Job Title *</label>
        <input id="title" name="title" required maxlength="255" value="{{ old('title', $job?->title) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        @error('title')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="job_category_id" class="mb-2 block text-sm font-medium">Job Category *</label>
        <select id="job_category_id" name="job_category_id" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            <option value="">Select category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('job_category_id', $job?->job_category_id) === $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('job_category_id')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="description" class="mb-2 block text-sm font-medium">Description *</label>
        <textarea id="description" name="description" rows="5" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('description', $job?->description) }}</textarea>
        @error('description')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="requirements" class="mb-2 block text-sm font-medium">Requirements *</label>
        <textarea id="requirements" name="requirements" rows="5" required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('requirements', $job?->requirements) }}</textarea>
        @error('requirements')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="location" class="mb-2 block text-sm font-medium">Location *</label>
        <input id="location" name="location" required maxlength="255" value="{{ old('location', $job?->location) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        @error('location')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 md:grid-cols-2">

        <div>
            <label for="employment_type" class="mb-2 block text-sm font-medium">Employment Type *</label>
            <select id="employment_type" name="employment_type" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">Select type</option>
                @foreach (\App\Enums\EmploymentType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('employment_type', $job?->employment_type?->value) === $type->value)>
                        {{ \Illuminate\Support\Str::headline($type->value) }}
                    </option>
                @endforeach
            </select>
            @error('employment_type')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="work_location_type" class="mb-2 block text-sm font-medium">Work Location Type *</label>
            <select id="work_location_type" name="work_location_type" required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">Select work location</option>
                @foreach (\App\Enums\WorkLocationType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('work_location_type', $job?->work_location_type?->value) === $type->value)>
                        {{ \Illuminate\Support\Str::headline($type->value) }}
                    </option>
                @endforeach
            </select>
            @error('work_location_type')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="experience_level" class="mb-2 block text-sm font-medium">Experience Level</label>
            <select id="experience_level" name="experience_level"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">Not specified</option>
                @foreach (\App\Enums\ExperienceLevel::cases() as $level)
                    <option value="{{ $level->value }}" @selected(old('experience_level', $job?->experience_level?->value) === $level->value)>
                        {{ \Illuminate\Support\Str::headline($level->value) }}
                    </option>
                @endforeach
            </select>
            @error('experience_level')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="application_deadline" class="mb-2 block text-sm font-medium">Application Deadline</label>
            <input type="date" id="application_deadline" name="application_deadline"
                value="{{ old('application_deadline', $job?->application_deadline?->format('Y-m-d')) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            @error('application_deadline')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="salary_min" class="mb-2 block text-sm font-medium">Minimum Salary</label>
            <input type="number" min="0" step="0.01" id="salary_min" name="salary_min"
                value="{{ old('salary_min', $job?->salary_min) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            @error('salary_min')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="salary_max" class="mb-2 block text-sm font-medium">Maximum Salary</label>
            <input type="number" min="0" step="0.01" id="salary_max" name="salary_max"
                value="{{ old('salary_max', $job?->salary_max) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            @error('salary_max')
            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        </div>

    </div>

    <div>
        <label for="required_skills" class="mb-2 block text-sm font-medium">Required Skills</label>
        <textarea id="required_skills" name="required_skills" rows="3"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('required_skills', $job?->required_skills) }}</textarea>
        @error('required_skills')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-5 dark:border-slate-800">
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            {{ $job ? 'Save Changes' : 'Publish Job' }}
        </button>

        <a href="{{ $job ? route('jobs.show', $job) : route('jobs.index') }}"
            class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
            Cancel
        </a>
    </div>
</div>