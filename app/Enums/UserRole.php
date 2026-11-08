<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case AdminGudang = 'admin_gudang';
    case AdminMarketplace = 'admin_marketplace';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner / Manajer',
            self::AdminGudang => 'Admin Gudang',
            self::AdminMarketplace => 'Admin Marketplace',
        };
    }
}
