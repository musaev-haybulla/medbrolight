<?php

declare(strict_types=1);

namespace App\Services\Telegram;

use App\Services\Telegram\Contracts\TelegramFileDownloaderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Psr\Log\LoggerInterface;

class TelegramFileDownloader implements TelegramFileDownloaderInterface
{
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 1000;

    public function __construct(
        private readonly string          $botToken,
        private readonly LoggerInterface $logger,
        private readonly string          $storageDir = 'telegram_files'
    ) {}

    public function downloadFile(string $fileId): string
    {
        try {
            // 1. Получаем информацию о файле от Telegram
            $fileInfo = $this->getFileInfo($fileId);
            if (!isset($fileInfo['file_path'])) {
                throw new RuntimeException("File path not found for file ID: {$fileId}");
            }

            // 2. Формируем URL для скачивания
            $downloadUrl = "https://api.telegram.org/file/bot{$this->botToken}/{$fileInfo['file_path']}";

            // 3. Генерируем локальный путь для сохранения
            $fileName = basename($fileInfo['file_path']);
            $localPath = "{$this->storageDir}/" . uniqid('tg_', true) . "_{$fileName}";

            // 4. Скачиваем файл
            $response = Http::retry(self::MAX_RETRIES, self::RETRY_DELAY_MS)
                ->timeout(30)
                ->get($downloadUrl);

            if (!$response->successful()) {
                throw new RuntimeException(
                    "Failed to download file. Status: {$response->status()}"
                );
            }

            // 5. Сохраняем файл
            Storage::put($localPath, $response->body());

            $this->logger->info('File downloaded successfully', [
                'file_id' => $fileId,
                'local_path' => $localPath,
                'size' => $response->header('Content-Length')
            ]);

            return $localPath;

        } catch (\Throwable $e) {
            $this->logger->error('File download failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);

            throw new RuntimeException(
                "Failed to download file ID: {$fileId}. Error: {$e->getMessage()}",
                previous: $e
            );
        }
    }

    private function getFileInfo(string $fileId): array
    {
        $response = Http::retry(self::MAX_RETRIES, self::RETRY_DELAY_MS)
            ->post("https://api.telegram.org/bot{$this->botToken}/getFile", [
                'file_id' => $fileId
            ]);

        if (!$response->successful()) {
            throw new RuntimeException(
                "Failed to get file info. Status: {$response->status()}"
            );
        }

        $data = $response->json();
        if (!isset($data['ok']) || !$data['ok']) {
            throw new RuntimeException(
                "Telegram API error: " . ($data['description'] ?? 'Unknown error')
            );
        }

        return $data['result'];
    }
}
