<?php

declare(strict_types=1);

namespace DaanDekker\ChromePdf\Exceptions;

class ChromeNotFoundException extends PdfException
{
    public static function atPath(string $path): static
    {
        return new static(
            "Chrome/Chromium executable not found at: {$path}. " .
            'Please install Chrome or set the correct path in config/pdf.php or PDF_CHROME_PATH environment variable.'
        );
    }

    public static function notExecutable(string $path): static
    {
        return new static("Chrome/Chromium at {$path} is not executable.");
    }
}
