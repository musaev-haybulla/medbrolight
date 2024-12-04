<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentStatus: string
{
    case NEW = 'new';
    case QUEUED = 'queued';
    case PROCESSING = 'processing';
    case FAILED = 'failed';
    case COMPLETED = 'completed';

    public function isTerminal(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canProcess(): bool
    {
        return match($this) {
            self::NEW, self::QUEUED => true,
            self::FAILED => true, // Failed можно обрабатывать повторно
            default => false
        };
    }

    public function getNextProcessingState(): self
    {
        return match($this) {
            self::NEW, self::QUEUED => self::PROCESSING,
            self::FAILED => self::QUEUED, // Failed переходит в Queued для повторной обработки
            default => throw new \LogicException("Cannot move to processing from state: {$this->value}")
        };
    }
}
