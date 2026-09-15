<?php

use App\Enums\ProductCategory;
use App\Models\Product;
use Artesaos\SEOTools\Facades\SEOMeta;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Layout('layouts::public')] class extends Component
{
    use WithPagination;

    #[Url]
    public string $category = '';

    #[Url]
    public string $country = '';

    public function mount(): void
    {
        SEOMeta::setTitle('Produk');
        SEOMeta::setDescription('Katalog snack bayi Yummy Bites dan beras impor dari PT Mustika Makmur Abadi.');
    }

    public function updatedCategory(): void
    {
        $this->country = '';
        $this->resetPage();
    }

    public function updatedCountry(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function countries()
    {
        return Product::query()
            ->where('category', ProductCategory::BerasImpor->value)
            ->whereNotNull('country_of_origin')
            ->distinct()
            ->orderBy('country_of_origin')
            ->pluck('country_of_origin');
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->where('is_active', true)
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->country, fn ($q) => $q->where('country_of_origin', $this->country))
            ->with('images')
            ->orderBy('name')
            ->paginate(9);
    }
}; ?>

<div class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
    <h1 class="text-3xl font-bold text-zinc-900 sm:text-4xl dark:text-white" data-aos="fade-up">Produk</h1>
    <p class="mt-3 max-w-2xl text-zinc-600 dark:text-zinc-400" data-aos="fade-up">
        Snack bayi Yummy Bites dan beras impor dari berbagai negara.
    </p>

    <div class="mt-8 flex flex-wrap gap-2" data-aos="fade-up">
        <button
            wire:click="$set('category', '')"
            class="rounded-full px-4 py-2 text-sm font-medium {{ $category === '' ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300' }}"
        >
            Semua
        </button>
        <button
            wire:click="$set('category', '{{ ProductCategory::YummyBites->value }}')"
            class="rounded-full px-4 py-2 text-sm font-medium {{ $category === ProductCategory::YummyBites->value ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300' }}"
        >
            Yummy Bites
        </button>
        <button
            wire:click="$set('category', '{{ ProductCategory::BerasImpor->value }}')"
            class="rounded-full px-4 py-2 text-sm font-medium {{ $category === ProductCategory::BerasImpor->value ? 'bg-zinc-900 text-white dark:bg-white dark:text-zinc-900' : 'border border-zinc-300 text-zinc-700 dark:border-zinc-700 dark:text-zinc-300' }}"
        >
            Beras Impor
        </button>
    </div>

    @if ($category === ProductCategory::BerasImpor->value && $this->countries->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-2" data-aos="fade-up">
            <button
                wire:click="$set('country', '')"
                class="rounded-full px-3 py-1 text-xs font-medium {{ $country === '' ? 'bg-amber-600 text-white' : 'border border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400' }}"
            >
                Semua Negara
            </button>
            @foreach ($this->countries as $originCountry)
                <button
                    wire:click="$set('country', '{{ $originCountry }}')"
                    class="rounded-full px-3 py-1 text-xs font-medium {{ $country === $originCountry ? 'bg-amber-600 text-white' : 'border border-zinc-300 text-zinc-600 dark:border-zinc-700 dark:text-zinc-400' }}"
                >
                    {{ $originCountry }}
                </button>
            @endforeach
        </div>
    @endif

    <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-aos="fade-up">
        @forelse ($this->products as $product)
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800" wire:key="product-{{ $product->id }}">
                @if ($product->images->isEmpty())
                    <div class="flex aspect-square items-center justify-center bg-amber-50 text-amber-600 dark:bg-amber-950 dark:text-amber-400">
                        <span class="text-4xl font-bold">{{ mb_substr($product->name, 0, 1) }}</span>
                    </div>
                @elseif ($product->images->count() === 1)
                    <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}" class="aspect-square w-full object-cover">
                @else
                    <div
                        x-data
                        x-init="new Swiper($refs.swiper, { modules: [SwiperModules.Navigation, SwiperModules.Pagination], navigation: true, pagination: { el: $refs.swiper.querySelector('.swiper-pagination') } })"
                        class="relative"
                    >
                        <div class="swiper aspect-square" x-ref="swiper">
                            <div class="swiper-wrapper">
                                @foreach ($product->images as $image)
                                    <div class="swiper-slide">
                                        <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $product->name }}" class="h-full w-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                @endif

                <div class="p-4">
                    <p class="font-semibold text-zinc-900 dark:text-white">{{ $product->name }}</p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <span class="inline-block rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                            {{ $product->category->label() }}
                        </span>
                        @if ($product->country_of_origin)
                            <span class="inline-block rounded-full bg-zinc-100 px-2 py-0.5 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                {{ $product->country_of_origin }}
                            </span>
                        @endif
                    </div>
                    @if ($product->description)
                        <p class="mt-2 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $product->description }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full text-sm text-zinc-500 dark:text-zinc-400">Belum ada produk untuk kategori ini.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $this->products->links() }}
    </div>
</div>
