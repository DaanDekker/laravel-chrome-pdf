<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\ServiceProvider;
use Mixsnoep\PdfPrint\Renderers\ChromeRenderer;
use Mixsnoep\PdfPrint\Renderers\RendererInterface;

final class PdfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pdf.php', 'pdf');

        $this->app->singleton(RendererInterface::class, function ($app) {
            return new ChromeRenderer(
                chromePath: config('pdf.chrome_path'),
                timeout: config('pdf.timeout', 60),
            );
        });

        $this->app->singleton(Pdf::class, function ($app) {
            return new Pdf(
                renderer: $app->make(RendererInterface::class),
                viewFactory: $app->make(ViewFactory::class),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/pdf.php' => config_path('pdf.php'),
            ], 'pdf-config');
        }
    }
}
