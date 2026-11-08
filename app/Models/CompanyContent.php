<?php

namespace App\Models;

use Database\Factories\CompanyContentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['section_key', 'title', 'body', 'image_path', 'meta', 'is_published', 'sort_order'])]
class CompanyContent extends Model
{
    /** @use HasFactory<CompanyContentFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public static function section(string $key): ?self
    {
        return static::where('section_key', $key)->where('is_published', true)->first();
    }
}
