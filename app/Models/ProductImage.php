<?php

namespace App\Models;

use Database\Factories\ProductImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['product_id', 'image_path', 'alt_text', 'sort_order'])]
class ProductImage extends Model
{
    /** @use HasFactory<ProductImageFactory> */
    use HasFactory;

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function url(): ?string
    {
        if (! Storage::disk('public')->exists($this->image_path)) {
            return null;
        }
        $url = asset('storage/'.$this->image_path);
        $path = Storage::disk('public')->path($this->image_path);
        $mtime = file_exists($path) ? filemtime($path) : time();

        return $url.'?v='.$mtime;
    }
}
