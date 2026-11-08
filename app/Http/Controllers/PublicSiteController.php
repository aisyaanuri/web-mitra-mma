<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use App\Models\CompanyContent;
use App\Models\ContactMessage;
use App\Models\Product;
use App\Models\SiteSetting;
use Artesaos\SEOTools\Facades\OpenGraph;
use Artesaos\SEOTools\Facades\SEOMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(): View
    {
        SEOMeta::setTitle('Beranda');
        SEOMeta::setDescription('Distributor resmi Yummy Bites dan beras premium di Sidoarjo, melayani 355+ outlet di Indonesia bagian timur.');
        OpenGraph::setTitle('PT Mustika Makmur Abadi');
        OpenGraph::setDescription('Distributor resmi Yummy Bites dan beras premium di Sidoarjo.');

        return view('pages.public.beranda', [
            'hero' => CompanyContent::section('beranda_hero'),
            'distribusi' => CompanyContent::section('beranda_distribusi'),
            'featuredProducts' => Product::where('is_active', true)->where('is_featured', true)->with('images')->take(8)->get(),
            'distributionRegions' => ['Jawa Timur', 'Sulawesi', 'Kalimantan', 'Timor Leste', 'Maluku', 'NTT', 'NTB', 'Bali'],
            'yummyBitesCount' => Product::where('is_active', true)->where('category', ProductCategory::YummyBites->value)->count(),
            'berasImporCount' => Product::where('is_active', true)->where('category', ProductCategory::BerasImpor->value)->count(),
        ]);
    }

    public function products(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'in:'.implode(',', array_column(ProductCategory::cases(), 'value'))],
            'country' => ['nullable', 'string', 'max:100'],
        ]);
        $search = $filters['search'] ?? '';
        $category = $filters['category'] ?? '';
        $country = $filters['country'] ?? '';

        SEOMeta::setTitle('Katalog Produk | PT Mustika Makmur Abadi');
        SEOMeta::setDescription('Katalog resmi snack & MPASI bayi Yummy Bites dan beras premium impor & lokal dari PT Mustika Makmur Abadi.');

        $products = Product::query()
            ->where('is_active', true)
            ->when($search, fn ($query) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            }))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($country, fn ($query) => $query->where('country_of_origin', $country))
            ->with('images')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('pages.public.produk', [
            'search' => $search,
            'category' => $category,
            'country' => $country,
            'products' => $products,
            'countries' => Product::query()
                ->where('category', ProductCategory::BerasImpor->value)
                ->whereNotNull('country_of_origin')
                ->distinct()
                ->orderBy('country_of_origin')
                ->pluck('country_of_origin'),
            'categoryIntro' => match ($category) {
                ProductCategory::YummyBites->value => 'Camilan dan MPASI sehat terpercaya untuk si kecil mulai usia 6 bulan hingga 1 tahun ke atas. Bebas gluten, tanpa bahan pengawet, dan bergizi tinggi.',
                ProductCategory::BerasImpor->value => 'Koleksi beras pilihan berkualitas tinggi: Beras Jepang Koshihikari, Beras Basmati India, Beras Melati Jasmine Thailand, Beras Organik Indonesia, dan Beras Lautan Mas.',
                default => null,
            },
            'whatsappNumber' => SiteSetting::get('whatsapp_number', '6281234567890'),
        ]);
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $product->load('images');

        $title = $product->name.' | PT Mustika Makmur Abadi';
        $description = mb_substr(str_replace("\n", ' ', $product->description ?? 'Katalog produk resmi PT Mustika Makmur Abadi'), 0, 160);
        SEOMeta::setTitle($title);
        SEOMeta::setDescription($description);
        SEOMeta::setCanonical(route('produk.detail', $product));
        OpenGraph::setTitle($title);
        OpenGraph::setDescription($description);
        OpenGraph::setUrl(route('produk.detail', $product));
        OpenGraph::addProperty('type', 'product');

        if ($firstImage = $product->images->first()) {
            OpenGraph::addImage($firstImage->url());
        }

        $number = SiteSetting::get('whatsapp_number', '6281234567890');
        $message = rawurlencode(
            "Halo PT Mustika Makmur Abadi, saya tertarik dan ingin menanyakan informasi pemesanan produk:\n\n".
            "Produk: {$product->name}\n".
            "SKU: {$product->sku}\n".
            'Kemasan: '.($product->weight ?? '-')."\n\n".
            'Mohon info ketersediaan stok dan penawaran terbaiknya. Terima kasih.'
        );

        return view('pages.public.produk-detail', [
            'product' => $product,
            'relatedProducts' => Product::query()
                ->where('is_active', true)
                ->where('id', '!=', $product->id)
                ->where('category', $product->category)
                ->with('images')
                ->inRandomOrder()
                ->take(3)
                ->get(),
            'whatsappLink' => "https://wa.me/{$number}?text={$message}",
        ]);
    }

    public function about(): View
    {
        SEOMeta::setTitle('Tentang Kami');
        SEOMeta::setDescription('Profil PT Mustika Makmur Abadi, distributor resmi Yummy Bites dan beras premium sejak 2019.');
        OpenGraph::setTitle('Tentang Kami - PT Mustika Makmur Abadi');

        $visiMisi = CompanyContent::section('tentang_kami_visi_misi');
        preg_match_all('/^\d+\.\s*(.+)$/m', $visiMisi?->body ?? '', $points);
        preg_match('/Visi\s*\n(.*?)\n\s*\n\s*Misi/is', $visiMisi?->body ?? '', $matches);

        return view('pages.public.tentang-kami', [
            'content' => CompanyContent::section('tentang_kami'),
            'visiMisi' => $visiMisi,
            'misiPoints' => array_map('trim', $points[1] ?? []),
            'visiText' => isset($matches[1]) ? trim($matches[1]) : null,
            'timeline' => [
                [
                    'year' => SiteSetting::get('established_year', '2019'),
                    'title' => 'Berdiri',
                    'desc' => 'Memulai usaha sebagai distributor resmi Yummy Bites dan beras premium di Sidoarjo, Jawa Timur.',
                ],
                [
                    'year' => 'Kini',
                    'title' => 'Terus Berkembang',
                    'desc' => SiteSetting::get('outlets_count', '355').'+ outlet di 8 wilayah Indonesia bagian timur, didukung '.SiteSetting::get('staff_count', '18').' tenaga ahli dan armada pengiriman sendiri.',
                ],
            ],
            'differentiators' => [
                ['title' => 'Distributor Resmi', 'desc' => 'Mitra resmi Yummy Bites & beras premium (impor & lokal) dari berbagai sumber terpercaya, bukan reseller.'],
                ['title' => 'Armada Sendiri', 'desc' => 'Gratis ongkir untuk area Surabaya Raya tanpa bergantung ekspedisi pihak ketiga.'],
                ['title' => '355+ Outlet', 'desc' => 'Jaringan distribusi aktif di 8 wilayah Indonesia bagian timur.'],
                ['title' => 'Berpengalaman', 'desc' => 'Melayani mitra bisnis sejak 2019 dengan 18 tenaga ahli distribusi.'],
            ],
        ]);
    }

    public function contact(): View
    {
        SEOMeta::setTitle('Kontak');
        SEOMeta::setDescription('Hubungi PT Mustika Makmur Abadi untuk kerja sama distribusi Yummy Bites dan beras premium.');

        return view('pages.public.kontak');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'max:2000'],
            'website' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($validated['website'] ?? null)) {
            ContactMessage::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'message' => $validated['message'],
                'ip_address' => $request->ip(),
            ]);
        }

        return to_route('kontak')->with('contact-submitted', true);
    }

    public function privacy(): View
    {
        SEOMeta::setTitle('Kebijakan Privasi');
        SEOMeta::setDescription('Kebijakan privasi PT Mustika Makmur Abadi mengenai data yang dikumpulkan melalui website ini.');

        return view('pages.public.kebijakan-privasi');
    }
}
