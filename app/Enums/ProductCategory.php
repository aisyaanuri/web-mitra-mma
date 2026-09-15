<?php

namespace App\Enums;

enum ProductCategory: string
{
    case YummyBites = 'yummy_bites';
    case BerasImpor = 'beras_impor';

    public function label(): string
    {
        return match ($this) {
            self::YummyBites => 'Yummy Bites',
            self::BerasImpor => 'Beras Impor',
        };
    }
}
