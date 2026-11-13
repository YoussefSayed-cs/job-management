@csrf

<div>
    <label for="name" class="mb-2 block text-sm font-medium">
        Category Name *
    </label>

    <input id="name" name="name" type="text" value="{{ old('name', $category?->name) }}" required maxlength="255"
        placeholder="e.g. Software Engineering"
        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">

    @error('name')
        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
    @enderror
</div>

<div class="flex items-center gap-3">
    <button type="submit"
        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
        {{ $submitLabel }}
    </button>

    <a href="{{ route('categories.index') }}"
        class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
        Cancel
    </a>
</div>