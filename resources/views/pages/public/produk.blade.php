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

    #[Computed]
    public function categoryIntro(): ?string
    {
        return match ($this->category) {
            ProductCategory::YummyBites->value => 'Camilan MPASI untuk si kecil usia 6 bulan+ hingga 1 tahun+.',
            ProductCategory::BerasImpor->value => 'Beras impor pilihan dari Jepang, Korea, India, Vietnam, dan Thailand.',
            default => null,
        };
    }
}; ?>

<div>
    {{-- Hero + filter inline: heading, short description, and category
    tabs share one section to reduce scroll distance. --}}
    <section class="bg-gradient-brand pb-8 pt-10 sm:pt-14">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <h1 class="heading-display font-display text-4xl font-extrabold text-white sm:text-5xl reveal">Produk Kami</h1>
            <p class="mt-4 max-w-xl text-lg text-white/80 reveal">
                Snack bayi Yummy Bites dan beras impor pilihan dari berbagai negara.
            </p>

            {{-- Category filter tabs --}}
            <div class="mt-6 flex flex-wrap gap-2 reveal">
                <button
                    wire:click="$set('category', '')"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $category === '' ? 'bg-white text-brand-700 shadow-lg' : 'bg-white/15 text-white hover:bg-white/25' }}"
                >
                    Semua
                </button>
                <button
                    wire:click="$set('category', '{{ ProductCategory::YummyBites->value }}')"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $category === ProductCategory::YummyBites->value ? 'bg-white text-brand-700 shadow-lg' : 'bg-white/15 text-white hover:bg-white/25' }}"
                >
                    Yummy Bites
                </button>
                <button
                    wire:click="$set('category', '{{ ProductCategory::BerasImpor->value }}')"
                    class="rounded-xl px-4 py-2.5 text-sm font-bold transition {{ $category === ProductCategory::BerasImpor->value ? 'bg-white text-brand-700 shadow-lg' : 'bg-white/15 text-white hover:bg-white/25' }}"
                >
                    Beras Impor
                </button>
            </div>

            @if ($this->categoryIntro)
                <p class="mt-3 rounded-xl bg-white/15 px-4 py-2.5 text-sm font-medium text-white/90 backdrop-blur-sm">{{ $this->categoryIntro }}</p>
            @endif

            @if ($category === ProductCategory::BerasImpor->value && $this->countries->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2">
                    <button
                        wire:click="$set('country', '')"
                        class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $country === '' ? 'bg-white text-brand-700' : 'bg-white/15 text-white hover:bg-white/25' }}"
                    >
                        Semua Negara
                    </button>
                    @foreach ($this->countries as $originCountry)
                        <button
                            wire:click="$set('country', '{{ $originCountry }}')"
                            class="rounded-lg px-3 py-1.5 text-xs font-bold transition {{ $country === $originCountry ? 'bg-white text-brand-700' : 'bg-white/15 text-white hover:bg-white/25' }}"
                        >
                            {{ $originCountry }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Product grid: 3-col desktop, 2-col tablet, 1-col mobile. Cards
    use rounded-2xl (identity motif), larger images, subtle hover. --}}
    <div class="mx-auto max-w-6xl px-4 pb-16 sm:px-6">
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->products as $product)
            <div class="overflow-hidden rounded-2xl bg-white shadow-md shadow-slate-200/60 transition hover:shadow-xl dark:bg-zinc-800 dark:shadow-none" wire:key="product-{{ $product->id }}">
                @if ($product->images->isEmpty())
                    <div class="flex aspect-square items-center justify-center bg-slate-50 text-brand-300 dark:bg-zinc-700/50">
                        <span class="font-display text-4xl font-bold">{{ mb_substr($product->name, 0, 1) }}</span>
                    </div>
                @elseif ($product->images->count() === 1)
                    <div class="flex aspect-square items-center justify-center bg-slate-50 dark:bg-zinc-700/50">
                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}" class="h-full w-full object-contain p-4" loading="lazy">
                    </div>
                @else
                    <div
                        x-data
                        x-init="new Swiper($refs.swiper, { modules: [SwiperModules.Navigation, SwiperModules.Pagination], navigation: true, pagination: { el: $refs.swiper.querySelector('.swiper-pagination') } })"
                        class="relative bg-slate-50 dark:bg-zinc-700/50"
                    >
                        <div class="swiper aspect-square" x-ref="swiper">
                            <div class="swiper-wrapper">
                                @foreach ($product->images as $image)
                                    <div class="swiper-slide">
                                        <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $product->name }}" class="h-full w-full object-contain p-4" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                @endif

                <div class="p-5">
                    <p class="font-bold text-slate-900 dark:text-white">{{ $product->name }}</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class="inline-block rounded-lg bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">
                            {{ $product->category->label() }}
                        </span>
                        @if ($product->country_of_origin)
                            <span class="inline-block rounded-lg bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600 dark:bg-zinc-700 dark:text-zinc-300">
                                {{ $product->country_of_origin }}
                            </span>
                        @endif
                    </div>
                    @if ($product->description)
                        <p class="mt-2.5 line-clamp-2 text-sm text-slate-500 dark:text-zinc-400">{{ $product->description }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full py-12 text-center text-sm text-slate-500 dark:text-zinc-400">Belum ada produk untuk kategori ini.</p>
        @endforelse
        </div>

        <div class="mt-8">
            {{ $this->products->links() }}
        </div>
    </div>
</div>
