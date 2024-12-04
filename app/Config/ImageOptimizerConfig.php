<?php
namespace App\Config;

final readonly class ImageOptimizerConfig
{
    public function __construct(
        public int $maxSize,
        public int $quality,
        public string $disk,
        public array $supportedFormats,
        public string $tempPath,
        public array $paths,
        public array $uploadLimits
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            maxSize: config('image.optimizer.max_size'),
            quality: config('image.optimizer.quality'),
            disk: config('image.optimizer.disk'),
            supportedFormats: config('image.optimizer.supported_formats'),
            tempPath: config('image.optimizer.temp_path'),
            paths: config('image.optimizer.paths'),
            uploadLimits: config('image.optimizer.upload_limits')
        );
    }
}