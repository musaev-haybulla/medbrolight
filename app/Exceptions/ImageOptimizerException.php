<?php
namespace App\Exceptions;

use RuntimeException;

class ImageOptimizerException extends RuntimeException
{
    public static function fileNotFound(string $path): self
    {
        return new self("Файл не найден: {$path}");
    }

    public static function unsupportedFormat(string $format): self
    {
        return new self("Формат {$format} не поддерживается");
    }

    public static function optimizationFailed(string $path, string $reason): self
    {
        return new self("Не удалось оптимизировать {$path}: {$reason}");
    }
}