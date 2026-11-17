@props([
    'sidebar' => false,
    'name' => 'Shagalni',
])

@if($sidebar)
    <flux:sidebar.brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 text-base font-bold text-white shadow-lg shadow-indigo-600/25">
            S
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-600 via-indigo-600 to-violet-700 text-base font-bold text-white shadow-lg shadow-indigo-600/25">
            S
        </x-slot>
    </flux:brand>
@endif
