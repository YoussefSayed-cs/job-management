@props([
    'sidebar' => false,
    'name' => 'Shagalni',
])

@if($sidebar)
    <flux:sidebar.brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-indigo-600 text-base font-bold text-white">
            S
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$name" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-indigo-600 text-base font-bold text-white">
            S
        </x-slot>
    </flux:brand>
@endif
