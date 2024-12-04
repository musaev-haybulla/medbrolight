<?php
namespace App\Services\Image;

use App\Config\ImageOptimizerConfig;
use App\Exceptions\ImageOptimizerException;
use Imagine\Image\Box;
use Imagine\Image\ImagineInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Imagine\Exception\RuntimeException as ImagineRuntimeException;

final class ImageOptimizer
{
    public function __construct(
        private readonly ImagineInterface $imagine,
        private readonly Filesystem $storage,
        private readonly ImageOptimizerConfig $config
    ) {}

    public function optimize(
        string $inputPath,
        string $outputPath,
        string $outputFormat,
        ?int $maxSize = null,
        ?int $quality = null
    ): void {
        // Валидация
        if (!in_array($outputFormat, $this->config->supportedFormats, true)) {
            throw ImageOptimizerException::unsupportedFormat($outputFormat);
        }

        if (!$this->storage->exists($inputPath)) {
            throw ImageOptimizerException::fileNotFound($inputPath);
        }

        try {
            // Загрузка изображения
            $imageContent = $this->storage->get($inputPath);
            $image = $this->imagine->load($imageContent);

            // Применение фильтров
            $effects = $image->effects();
            $effects
                ->grayscale()
                ->gamma(1.2)
                ->brightness(10)
                ->sharpen();

            // Изменение размера если нужно
            $this->resizeIfNeeded(
                $image,
                $maxSize ?? $this->config->maxSize
            );

            // Сохранение с оптимизацией
            $options = $this->getSaveOptions(
                $outputFormat,
                $quality ?? $this->config->quality
            );

            $this->storage->put(
                $outputPath,
                $image->get($outputFormat, $options)
            );

            // Логирование результатов
            $this->logOptimizationResult($inputPath, $outputPath);
        } catch (ImagineRuntimeException $e) {
            $message = "Image optimization failed: " . $e->getMessage();
            Log::error($message, [
                'input' => $inputPath,
                'output' => $outputPath,
                'format' => $outputFormat
            ]);
            throw ImageOptimizerException::optimizationFailed($inputPath, $e->getMessage());
        }
    }

    private function resizeIfNeeded($image, int $maxSize): void
    {
        $size = $image->getSize();
        $width = $size->getWidth();
        $height = $size->getHeight();

        if (max($width, $height) > $maxSize) {
            $ratio = $maxSize / max($width, $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
            $image->resize(new Box($newWidth, $newHeight));
        }
    }

    private function getSaveOptions(string $format, int $quality): array
    {
        return match (strtolower($format)) {
            'webp' => [
                'webp_quality' => $quality,
                'webp_lossless' => false
            ],
            'heic' => [
                'heic_quality' => $quality,
            ],
            default => [
                'jpeg_quality' => $quality,
                'strip' => true
            ]
        };
    }

    private function logOptimizationResult(string $inputPath, string $outputPath): void
    {
        $inputSize = $this->storage->size($inputPath);
        $outputSize = $this->storage->size($outputPath);
        $compression = round(($inputSize - $outputSize) / $inputSize * 100, 2);

        Log::info('Image optimization completed', [
            'input_size' => $inputSize,
            'output_size' => $outputSize,
            'compression' => $compression . '%'
        ]);
    }
}
