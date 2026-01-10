<?php

declare(strict_types=1);

namespace DaanDekker\ChromePdf\Renderers;

use DaanDekker\ChromePdf\Exceptions\ChromeNotFoundException;
use DaanDekker\ChromePdf\Exceptions\PdfException;
use DaanDekker\ChromePdf\Process\Process;
use DaanDekker\ChromePdf\Support\TemporaryFile;

final class ChromeRenderer implements RendererInterface
{
    private bool $validated = false;

    public function __construct(
        private readonly string $chromePath,
        private readonly int $timeout = 60,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function render(string $html, string $outputPath, array $options = []): void
    {
        if (! $this->validated) {
            $this->validateChromePath();
            $this->validated = true;
        }

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
            $command[] = '--no-pdf-header-footer';
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
