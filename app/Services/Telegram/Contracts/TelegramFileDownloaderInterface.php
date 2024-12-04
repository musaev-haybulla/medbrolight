<?php

namespace App\Services\Telegram\Contracts;

interface TelegramFileDownloaderInterface
{
    public function downloadFile(string $fileId): string;
}
