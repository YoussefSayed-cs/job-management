@props([
    'sidebar' => false,
    'name' => 'Shagalni',
])

@if($sidebar)
    <flux:sidebar.brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold tracking-tight text-white shadow-sm shadow-indigo-600/30 dark:bg-indigo-500">
            S
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-xl bg-indigo-600 text-base font-bold tracking-tight text-white shadow-sm shadow-indigo-600/30 dark:bg-indigo-500">
            S
        </x-slot>
    </flux:brand>
@endif