@props(['flip' => false, 'color' => 'text-white dark:text-zinc-900'])

<div class="{{ $flip ? 'rotate-180' : '' }} {{ $color }} leading-none" aria-hidden="true">
    <svg viewBox="0 0 1440 80" class="h-10 w-full sm:h-16" preserveAspectRatio="none" fill="currentColor">
        <path d="M0,32 C240,80 480,0 720,24 C960,48 1200,88 1440,40 L1440,80 L0,80 Z" />
    </svg>
</div>
