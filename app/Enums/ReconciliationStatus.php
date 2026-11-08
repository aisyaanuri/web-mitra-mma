<?php

namespace App\Enums;

enum ReconciliationStatus: string
{
    case Sesuai = 'sesuai';
    case Selisih = 'selisih';
    case BelumDicek = 'belum_dicek';

    public function label(): string
    {
        return match ($this) {
            self::Sesuai => 'Sesuai',
            self::Selisih => 'Selisih',
            self::BelumDicek => 'Belum Dicek',
        };
    }
}
