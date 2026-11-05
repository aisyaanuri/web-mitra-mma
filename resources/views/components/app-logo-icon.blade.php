<img
    src="{{ asset('images/logo-mustika-square.webp') }}"
    alt="{{ config('app.name', 'PT Mustika Makmur Abadi') }}"
    {{ $attributes->except(['fill-current'])->merge(['class' => 'rounded-md object-contain']) }}
/>
