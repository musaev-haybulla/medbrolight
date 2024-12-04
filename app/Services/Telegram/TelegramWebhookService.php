<?php
namespace App\Services\Telegram;

use App\Models\Document;
use App\Models\File;
use App\Enums\DocumentStatus;
use App\Enums\FileType;
use Illuminate\Support\Facades\DB;

class TelegramWebhookService
{
    public function process(array $webhookData): Document
    {
        return DB::transaction(function () use ($webhookData) {
            $document = Document::create([
                'telegram_message_id' => $webhookData['message_id'],
                'user_id' => $webhookData['user_id'],
                'status' => DocumentStatus::NEW
            ]);

            if (isset($webhookData['document'])) {
                File::create([
                    'document_id' => $document->id,
                    'file_id' => $webhookData['document']['file_id'],
                    'file_type' => FileType::PDF,
                    'file_path' => '',
                    'file_order' => 0
                ]);
            }

            if (isset($webhookData['photo'])) {
                foreach ($webhookData['photo'] as $index => $photo) {
                    File::create([
                        'document_id' => $document->id,
                        'file_id' => $photo['file_id'],
                        'file_type' => FileType::IMAGE,
                        'file_path' => '',
                        'file_order' => $index
                    ]);
                }
            }

            return $document;
        });
    }
}
