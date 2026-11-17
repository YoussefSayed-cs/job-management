<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="relative min-h-[calc(100vh-4rem)] bg-transparent px-4 py-6 sm:px-6 lg:px-10 lg:py-8">
        <div class="mx-auto w-full max-w-screen-2xl">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>