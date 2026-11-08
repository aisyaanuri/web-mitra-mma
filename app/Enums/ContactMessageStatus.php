<?php

namespace App\Enums;

enum ContactMessageStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Replied = 'replied';

    public function label(): string
    {
        return match ($this) {
            self::New => 'Baru',
            self::Read => 'Sudah Dibaca',
            self::Replied => 'Sudah Dibalas',
        };
    }
}
