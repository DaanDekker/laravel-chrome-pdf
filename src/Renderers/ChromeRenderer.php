<?php

declare(strict_types=1);

namespace DaanDekker\ChromePdf\Renderers;

use DaanDekker\ChromePdf\Exceptions\ChromeNotFoundException;
use DaanDekker\ChromePdf\Exceptions\PdfException;
use DaanDekker\ChromePdf\Process\Process;
use DaanDekker\ChromePdf\Support\TemporaryFile;

final class ChromeRenderer implements RendererInterface
{
    public function __construct(
        private readonly string $chromePath,
        private readonly int $timeout = 60,
    ) {
        $this->validateChromePath();
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function render(string $html, string $outputPath, array $options = []): void
    {
        $tempFile = TemporaryFile::fromHtml($html);

        try {
            $command = $this->buildCommand($tempFile->url(), $outputPath, $options);

            Process::fromCommand($command)
                ->setTimeout($this->timeout)
                ->runOrFail();

            if (! file_exists($outputPath)) {
                throw PdfException::outputNotFound($outputPath);
            }
        } finally {
            $tempFile->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<int, string>
     */
    private function buildCommand(string $inputUrl, string $outputPath, array $options): array
    {
        $command = [
            $this->chromePath,
            '--headless',
            '--disable-gpu',
            '--disable-software-rasterizer',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--disable-extensions',
            '--disable-background-networking',
            '--run-all-compositor-stages-before-draw',
        ];

        $command[] = '--print-to-pdf=' . $outputPath;

        if (! isset($options['header']) && ! isset($options['footer'])) {
            $command[] = '--print-to-pdf-no-header';
        }

        if (isset($options['format'])) {
            $this->getPaperSize($options['format']);
        }

        if (isset($options['orientation']) && $options['orientation'] === 'landscape') {
            $command[] = '--landscape';
        }

        if ($options['print_background'] ?? true) {
            $command[] = '--print-background';
        }

        if (isset($options['wait'])) {
            $command[] = '--virtual-time-budget=' . (int) $options['wait'];
        }

        if (isset($options['scale'])) {
            $command[] = '--scale=' . (float) $options['scale'];
        }

        $command[] = $inputUrl;

        return $command;
    }

    /**
     * @return array{width: float, height: float}|null
     */
    private function getPaperSize(string $format): ?array
    {
        $sizes = [
            'A4' => ['width' => 8.27, 'height' => 11.69],
            'A3' => ['width' => 11.69, 'height' => 16.54],
            'A5' => ['width' => 5.83, 'height' => 8.27],
            'Letter' => ['width' => 8.5, 'height' => 11],
            'Legal' => ['width' => 8.5, 'height' => 14],
            'Tabloid' => ['width' => 11, 'height' => 17],
        ];

        return $sizes[ucfirst(strtolower($format))] ?? null;
    }

    private function validateChromePath(): void
    {
        if (! file_exists($this->chromePath)) {
            throw ChromeNotFoundException::atPath($this->chromePath);
        }

        if (! is_executable($this->chromePath)) {
            throw ChromeNotFoundException::notExecutable($this->chromePath);
        }
    }
}
