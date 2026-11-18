<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="relative min-w-0 p-0! lg:p-0!">

        <x-app-topbar :title="$title ?? null" />

        <div class="mx-auto w-full max-w-screen-2xl px-4 py-5 sm:px-6 sm:py-7 lg:px-8 lg:py-8">
            {{ $slot }}
        </div>

    </flux:main>
</x-layouts::app.sidebar>