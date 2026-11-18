@props(['title' => null])

<x-layouts::app :title="$title">
    @if (session('status'))
        <div role="status"
            class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    {{ $slot }}
</x-layouts::app>