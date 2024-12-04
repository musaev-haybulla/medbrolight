<?php

namespace App\Http\Controllers;

use App\Models\{Document, File};
use App\Enums\{DocumentStatus, FileType};
use App\Jobs\ProcessDocumentFiles;
use App\Http\Requests\TelegramWebhookRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TelegramWebhookController extends Controller
{
    public function handle(TelegramWebhookRequest $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request) {
                // Создаем запись Document
                $document = Document::create([
                    'telegram_message_id' => $request->getMessageId(),
                    'user_id' => $request->getUserId(),
                    'status' => DocumentStatus::NEW
                ]);

                // Создаем записи File для каждого файла
                foreach ($request->getFiles() as $index => $file) {
                    File::create([
                        'document_id' => $document->id,
                        'file_id' => $file['file_id'],
                        'file_type' => FileType::from($file['type']),
                        'file_order' => $index + 1
                    ]);
                }

                // Отправляем в очередь
                ProcessDocumentFiles::dispatch($document->id);
            });

            return response()->json(['status' => 'ok']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['status' => 'error'], 500);
        }
    }
}
