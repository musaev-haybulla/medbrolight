<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\{Document, File};
use App\Enums\{DocumentStatus, FileStatus}; // Добавим enum для статусов файлов
use App\Services\Telegram\TelegramFileDownloader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Throwable;

final class ProcessDocumentFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $documentId
    ) {}

    public function handle(
        TelegramFileDownloader $downloader,
        LoggerInterface $logger
    ): void {
        // 1. Получаем документ со связанными файлами
        $document = Document::with('files')->findOrFail($this->documentId);

        if (!$document->status->canProcess()) {
            return;
        }

        try {
            // 2. Обновляем статус на PROCESSING
            $document->status = DocumentStatus::PROCESSING;
            $document->save();

            $hasErrors = false;

            // 3. Обрабатываем каждый файл отдельно
            foreach ($document->files as $file) {
                try {
                    $this->processFile($file, $downloader, $logger);
                } catch (Throwable $e) {
                    $hasErrors = true;
                    $this->markFileAsFailed($file, $e->getMessage(), $logger);

                    // Логируем ошибку, но продолжаем обработку остальных файлов
                    $logger->error('Failed to process file', [
                        'document_id' => $this->documentId,
                        'file_id' => $file->file_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // 4. Определяем финальный статус документа
            $finalStatus = $this->determineFinalStatus($document);
            $document->status = $finalStatus;
            $document->save();

            if ($hasErrors && $this->attempts() < 3) {
                // Если есть ошибки и попытки не исчерпаны - планируем повторную обработку
                $this->release(60 * pow(2, $this->attempts()));
                return;
            }

        } catch (Throwable $e) {
            $this->handleCriticalError($e, $document, $logger);
        }
    }

    private function processFile(
        File $file,
        TelegramFileDownloader $downloader,
        LoggerInterface $logger
    ): void {
        // Используем транзакцию только для операций с БД
        DB::transaction(function () use ($file, $downloader, $logger) {
            // Сначала пометим файл как обрабатываемый
            $file->status = FileStatus::PROCESSING;
            $file->save();

            // Скачиваем файл
            $filePath = $downloader->downloadFile($file->file_id);

            // Обновляем информацию о файле
            $file->file_path = $filePath;
            $file->status = FileStatus::COMPLETED;
            $file->save();

            $logger->info('File processed successfully', [
                'document_id' => $this->documentId,
                'file_id' => $file->file_id
            ]);
        });
    }

    private function markFileAsFailed(
        File $file,
        string $error,
        LoggerInterface $logger
    ): void {
        DB::transaction(function () use ($file, $error) {
            $file->status = FileStatus::FAILED;
            $file->error_message = $error;
            $file->save();
        });
    }

    private function determineFinalStatus(Document $document): DocumentStatus
    {
        $fileStatuses = $document->files->pluck('status');

        if ($fileStatuses->every(fn($status) => $status === FileStatus::COMPLETED)) {
            return DocumentStatus::COMPLETED;
        }

        if ($fileStatuses->every(fn($status) => $status === FileStatus::FAILED)) {
            return DocumentStatus::FAILED;
        }

        return DocumentStatus::PROCESSING;
    }

    private function handleCriticalError(
        Throwable $e,
        Document $document,
        LoggerInterface $logger
    ): void {
        $logger->emergency('Critical error while processing document', [
            'document_id' => $this->documentId,
            'error' => $e->getMessage()
        ]);

        DB::transaction(function () use ($document, $e) {
            $document->status = DocumentStatus::FAILED;
            $document->error_message = "Critical error: " . $e->getMessage();
            $document->save();
        });

        throw $e;
    }
}
