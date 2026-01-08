<?php

declare(strict_types=1);

namespace DaanDekker\ChromePdf\Exceptions;

use Exception;

class PdfException extends Exception
{
    public static function renderFailed(string $reason): static
    {
        return new static("PDF rendering failed: {$reason}");
    }

    public static function fileNotWritable(string $path): static
    {
        return new static("Cannot write PDF to path: {$path}");
    }

    public static function outputNotFound(string $path): static
    {
        return new static("PDF was not generated at expected path: {$path}");
    }
}
