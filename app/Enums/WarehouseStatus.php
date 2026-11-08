<?php

namespace App\Enums;

enum WarehouseStatus: string
{
    case Diterima = 'diterima';
    case Dikemas = 'dikemas';
    case SiapKirim = 'siap_kirim';
    case Dikirim = 'dikirim';

    public function label(): string
    {
        return match ($this) {
            self::Diterima => 'Diterima',
            self::Dikemas => 'Dikemas',
            self::SiapKirim => 'Siap Kirim',
            self::Dikirim => 'Dikirim',
        };
    }

    /**
     * The status that follows this one in the normal warehouse flow, or
     * null if this is already the final status.
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Diterima => self::Dikemas,
            self::Dikemas => self::SiapKirim,
            self::SiapKirim => self::Dikirim,
            self::Dikirim => null,
        };
    }
}
