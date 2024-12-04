<?php

declare(strict_types=1);

namespace App\Enums;

enum FileType: string
{
    case IMAGE = 'image';
    case PDF = 'pdf';

    public function isImage(): bool
    {
        return $this === self::IMAGE;
    }

    public function isPdf(): bool
    {
        return $this === self::PDF;
    }
}
