<?php

namespace App\Models;

use App\Enums\ProductCategory;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'slug', 'sku', 'category', 'country_of_origin', 'description',
    'weight', 'is_active', 'is_featured', 'meta_title', 'meta_description',
])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'category' => ProductCategory::class,
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /** @return HasMany<ProductImage, $this> */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /** @return HasMany<OrderItem, $this> */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return array{main: string, details: array<int, array{label: string, value: string}>}
     */
    public function parsedDetails(): array
    {
        if (! $this->description) {
            return ['main' => '', 'details' => []];
        }

        $lines = explode("\n", str_replace("\r", '', $this->description));
        $main = [];
        $details = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (! $trimmed) {
                continue;
            }

            if (preg_match('/^(Komposisi|Alergen|Rekomendasi Sajian|Tekstur|Saran konsumsi):\s*(.+)$/i', $trimmed, $m)) {
                $val = $m[2];
                if (stripos($val, 'Alergen:') !== false) {
                    $parts = preg_split('/(?:\.|\n)\s*Alergen:\s*/i', $val);
                    if ($parts !== false) {
                        $details[] = ['label' => $m[1], 'value' => trim($parts[0])];
                        if (isset($parts[1])) {
                            $details[] = ['label' => 'Alergen', 'value' => trim($parts[1], " \t\n\r\0\x0B.")];
                        }
                    }
                } else {
                    $details[] = ['label' => $m[1], 'value' => trim($val, " \t\n\r\0\x0B.")];
                }
            } else {
                $main[] = $trimmed;
            }
        }

        return [
            'main' => implode(' ', $main),
            'details' => $details,
        ];
    }
}
