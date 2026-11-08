<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductCategory;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = $filters['search'] ?? '';

        return view('pages.admin.products.index', [
            'search' => $search,
            'products' => Product::query()
                ->when($search, fn ($query) => $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                }))
                ->withCount('images')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.products.form', ['product' => null]);
    }

    public function edit(Product $product): View
    {
        return view('pages.admin.products.form', ['product' => $product->load('images')]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->saveProduct($request, new Product);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        return $this->saveProduct($request, $product);
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('status', __('Status produk diperbarui.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        $product->delete();

        return to_route('admin.products.index')->with('status', __('Produk ":name" dihapus.', ['name' => $product->name]));
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        abort_unless($image->product_id === $product->id, 404);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return back()->with('status', __('Foto produk dihapus.'));
    }

    private function saveProduct(Request $request, Product $product): RedirectResponse
    {
        $request->merge([
            'slug' => $request->input('slug') ?: Str::slug($request->input('name', '')),
            'is_active' => $request->boolean('is_active'),
            'is_featured' => $request->boolean('is_featured'),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($product->id)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('products', 'sku')->ignore($product->id)],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'country_of_origin' => ['nullable', 'required_if:category,'.ProductCategory::BerasImpor->value, 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'weight' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'newImages' => ['nullable', 'array', 'max:5'],
            'newImages.*' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], [
            'newImages.max' => __('Maksimal 5 foto yang dapat diunggah sekaligus.'),
            'newImages.*.image' => __('File yang diunggah harus berupa file gambar yang valid.'),
            'newImages.*.mimes' => __('Format file tidak diizinkan. Hanya format JPG, JPEG, PNG, GIF, dan WEBP yang diperbolehkan.'),
            'newImages.*.max' => __('Ukuran gambar terlalu besar. Maksimal ukuran per file adalah 2 MB (2048 KB).'),
        ]);

        if ($validated['category'] !== ProductCategory::BerasImpor->value) {
            $validated['country_of_origin'] = null;
        }

        $images = $validated['newImages'] ?? [];
        $product->fill(collect($validated)->except('newImages')->toArray());
        $product->save();

        foreach ($images as $index => $file) {
            $product->images()->create([
                'image_path' => $file->store('products', 'public'),
                'alt_text' => $product->name,
                'sort_order' => $product->images()->count() + $index,
            ]);
        }

        return to_route('admin.products.index')->with('status', __('Produk disimpan.'));
    }
}
