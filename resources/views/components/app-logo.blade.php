@props([
    'sidebar' => false,
    'name' => 'Shagalni',
])

@if($sidebar)
    <flux:sidebar.brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-xl border border-teal-600/40 bg-teal-700 text-base font-bold tracking-tight text-white shadow-xs dark:border-teal-500/40 dark:bg-teal-600">
            S
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-xl border border-teal-600/40 bg-teal-700 text-base font-bold tracking-tight text-white shadow-xs dark:border-teal-500/40 dark:bg-teal-600">
            S
        </x-slot>
    </flux:brand>
@endif