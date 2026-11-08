@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'PT Mustika Makmur Abadi')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg overflow-hidden bg-white shadow-xs">
            <x-app-logo-icon class="size-7 object-contain" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('app.name', 'PT Mustika Makmur Abadi')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-lg overflow-hidden bg-white shadow-xs">
            <x-app-logo-icon class="size-7 object-contain" />
        </x-slot>
    </flux:brand>
@endif
