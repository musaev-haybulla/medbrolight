<?php

namespace App\Providers;

use App\Services\Telegram\Contracts\TelegramFileDownloaderInterface;
use App\Services\Telegram\TelegramFileDownloader;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TelegramFileDownloaderInterface::class, function ($app) {
            return new TelegramFileDownloader(
                botToken: config('services.telegram.bot_token'),
                logger: $app->make(LoggerInterface::class)
            );
        });
    }
}
