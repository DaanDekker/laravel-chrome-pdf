<?php

declare(strict_types=1);

namespace DaanDekker\ChromePdf\Facades;

use Illuminate\Support\Facades\Facade;
use DaanDekker\ChromePdf\Pdf as PdfManager;
use DaanDekker\ChromePdf\PdfBuilder;

/**
 * @method static PdfBuilder view(string $view, array $data = [])
 * @method static PdfBuilder html(string $html)
 * @method static PdfBuilder createBuilder()
 *
 * @see PdfManager
 */
final class Pdf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PdfManager::class;
    }
}
