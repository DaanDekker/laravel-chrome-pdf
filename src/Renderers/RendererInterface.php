<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint\Renderers;

interface RendererInterface
{
    /**
     * Render HTML content to a PDF file.
     *
     * @param  array<string, mixed>  $options
     */
    public function render(string $html, string $outputPath, array $options = []): void;
}
