<?php

namespace App\Models;

use Database\Factories\ShopeeTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $access_expires_at
 * @property Carbon $refresh_expires_at
 * @property Carbon|null $last_refreshed_at
 */
#[Fillable([
    'shop_id', 'access_token', 'refresh_token',
    'access_expires_at', 'refresh_expires_at', 'last_refreshed_at',
])]
#[Hidden(['access_token', 'refresh_token'])]
class ShopeeToken extends Model
{
    /** @use HasFactory<ShopeeTokenFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'access_expires_at' => 'datetime',
            'refresh_expires_at' => 'datetime',
            'last_refreshed_at' => 'datetime',
        ];
    }

    public function needsRefresh(): bool
    {
        return $this->access_expires_at->isPast();
    }
}
