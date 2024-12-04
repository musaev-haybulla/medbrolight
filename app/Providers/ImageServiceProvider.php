<?php
namespace App\Providers;

use App\Services\Image\ImageOptimizer;
use App\Config\ImageOptimizerConfig;
use Illuminate\Support\ServiceProvider;
use Imagine\Gd\Imagine;

class ImageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрация конфига как singleton
        $this->app->singleton(ImageOptimizerConfig::class, function ($app) {
            return ImageOptimizerConfig::fromConfig();
        });

        // Регистрация оптимизатора
        $this->app->bind(ImageOptimizer::class, function ($app) {
            return new ImageOptimizer(
                new Imagine(),
                $app['filesystem']->disk($app[ImageOptimizerConfig::class]->disk),
                $app[ImageOptimizerConfig::class]
            );
        });
    }

    public function boot(): void
    {
        // Публикация конфига
        $this->publishes([
            __DIR__.'/../../config/image.php' => config_path('image.php'),
        ], 'config');
    }
}