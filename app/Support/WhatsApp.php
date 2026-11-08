<?php

namespace App\Support;

use App\Models\Product;
use App\Models\SiteSetting;

class WhatsApp
{
    public static function productLink(Product $product): string
    {
        $number = SiteSetting::get('whatsapp_number', '6281234567890');
        $message = rawurlencode(
            "Halo PT Mustika Makmur Abadi, saya tertarik untuk menanyakan produk berikut:\n\n".
            "Produk: {$product->name}\n".
            "SKU: {$product->sku}\n".
            'Kemasan: '.($product->weight ?? '-')."\n\n".
            'Mohon info ketersediaan stok dan harga grosirnya. Terima kasih.'
        );

        return "https://wa.me/{$number}?text={$message}";
    }
}
