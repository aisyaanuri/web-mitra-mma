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
    {{-- Hero --}}
    <section class="bg-brand-900">
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6" data-aos="fade-up">
            <h1 class="heading-display font-display text-4xl font-bold text-white sm:text-5xl">Produk Kami</h1>
            <p class="mt-4 max-w-xl text-brand-100">
                Snack bayi Yummy Bites dan beras impor pilihan dari berbagai negara.
            </p>
        </div>
    </section>

    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6">
        <div class="flex flex-wrap gap-2" data-aos="fade-up">
            <button
                wire:click="$set('category', '')"
                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === '' ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'border border-zinc-200 text-zinc-700 hover:border-brand-300 dark:border-zinc-700 dark:text-zinc-300' }}"
            >
                Semua
            </button>
            <button
                wire:click="$set('category', '{{ ProductCategory::YummyBites->value }}')"
                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === ProductCategory::YummyBites->value ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'border border-zinc-200 text-zinc-700 hover:border-brand-300 dark:border-zinc-700 dark:text-zinc-300' }}"
            >
                Yummy Bites
            </button>
            <button
                wire:click="$set('category', '{{ ProductCategory::BerasImpor->value }}')"
                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $category === ProductCategory::BerasImpor->value ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'border border-zinc-200 text-zinc-700 hover:border-brand-300 dark:border-zinc-700 dark:text-zinc-300' }}"
            >
                Beras Impor
            </button>
        </div>

        @if ($this->categoryIntro)
            <p class="mt-4 rounded-lg bg-brand-50 px-4 py-2.5 text-sm font-medium text-brand-700 dark:bg-brand-900/30 dark:text-brand-300" data-aos="fade-up">{{ $this->categoryIntro }}</p>
        @endif

        @if ($category === ProductCategory::BerasImpor->value && $this->countries->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2" data-aos="fade-up">
                <button
                    wire:click="$set('country', '')"
                    class="rounded-full px-3 py-1 text-xs font-medium transition {{ $country === '' ? 'bg-brand-600 text-white' : 'border border-zinc-200 text-zinc-600 hover:border-brand-300 dark:border-zinc-700 dark:text-zinc-400' }}"
                >
                    Semua Negara
                </button>
                @foreach ($this->countries as $originCountry)
                    <button
                        wire:click="$set('country', '{{ $originCountry }}')"
                        class="rounded-full px-3 py-1 text-xs font-medium transition {{ $country === $originCountry ? 'bg-brand-600 text-white' : 'border border-zinc-200 text-zinc-600 hover:border-brand-300 dark:border-zinc-700 dark:text-zinc-400' }}"
                    >
                        {{ $originCountry }}
                    </button>
                @endforeach
            </div>
        @endif

        <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($this->products as $product)
            <div class="overflow-hidden rounded-xl border border-zinc-100 bg-white transition hover:border-brand-200 dark:border-zinc-800 dark:bg-zinc-900" wire:key="product-{{ $product->id }}">
                @if ($product->images->isEmpty())
                    <div class="flex aspect-square items-center justify-center bg-zinc-50 text-brand-300 dark:bg-zinc-800">
                        <span class="font-display text-4xl">{{ mb_substr($product->name, 0, 1) }}</span>
                    </div>
                @elseif ($product->images->count() === 1)
                    <div class="flex aspect-square items-center justify-center bg-zinc-50 dark:bg-zinc-800">
                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->images->first()->alt_text ?? $product->name }}" class="h-full w-full object-contain p-4">
                    </div>
                @else
                    <div
                        x-data
                        x-init="new Swiper($refs.swiper, { modules: [SwiperModules.Navigation, SwiperModules.Pagination], navigation: true, pagination: { el: $refs.swiper.querySelector('.swiper-pagination') } })"
                        class="relative bg-zinc-50 dark:bg-zinc-800"
                    >
                        <div class="swiper aspect-square" x-ref="swiper">
                            <div class="swiper-wrapper">
                                @foreach ($product->images as $image)
                                    <div class="swiper-slide">
                                        <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?? $product->name }}" class="h-full w-full object-contain p-4">
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                @endif

                <div class="p-5">
                    <p class="font-bold text-zinc-900 dark:text-white">{{ $product->name }}</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span class="inline-block rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                            {{ $product->category->label() }}
                        </span>
                        @if ($product->country_of_origin)
                            <span class="inline-block rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                {{ $product->country_of_origin }}
                            </span>
                        @endif
                    </div>
                    @if ($product->description)
                        <p class="mt-2.5 line-clamp-2 text-sm text-zinc-500 dark:text-zinc-400">{{ $product->description }}</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="col-span-full py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">Belum ada produk untuk kategori ini.</p>
        @endforelse
        </div>

        <div class="mt-8">
            {{ $this->products->links() }}
        </div>
    </div>
</div>
