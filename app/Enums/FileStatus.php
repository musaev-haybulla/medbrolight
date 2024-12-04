<?php

declare(strict_types=1);

namespace App\Enums;

enum FileStatus: string
{
    case NEW = 'new';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
    case COMPLETED = 'completed';

    public function canProcess(): bool
    {
        return $this === self::NEW || $this === self::FAILED;
    }
}
