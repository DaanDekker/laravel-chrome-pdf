<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint\Tests;

use Mixsnoep\PdfPrint\PdfServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PdfServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Pdf' => \Mixsnoep\PdfPrint\Facades\Pdf::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        // Set a mock chrome path for testing
        $app['config']->set('pdf.chrome_path', '/usr/bin/chromium');
        $app['config']->set('pdf.timeout', 30);
    }
}
