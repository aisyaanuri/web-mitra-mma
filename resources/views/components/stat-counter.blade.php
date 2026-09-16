@props([
    'target',
    'suffix' => '',
    'label',
    'duration' => 1500,
    'numberClass' => 'text-brand-600 dark:text-brand-300',
    'labelClass' => 'text-zinc-500 dark:text-zinc-400',
])

<div
    x-data="{ count: 0 }"
    x-intersect.once="
        const target = {{ (int) $target }};
        const duration = {{ (int) $duration }};
        let start = null;
        const step = (timestamp) => {
            if (! start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            count = Math.floor(progress * target);
            if (progress < 1) requestAnimationFrame(step);
            else count = target;
        };
        requestAnimationFrame(step);
    "
    {{ $attributes }}
>
    <p class="text-3xl font-bold {{ $numberClass }} sm:text-4xl" x-text="count + '{{ $suffix }}'"></p>
    <p class="mt-1 text-sm {{ $labelClass }}">{{ $label }}</p>
</div>
