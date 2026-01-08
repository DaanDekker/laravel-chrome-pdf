<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Mixsnoep\PdfPrint\Renderers\RendererInterface;

final class Pdf
{
    public function __construct(
        private readonly RendererInterface $renderer,
        private readonly ViewFactory $viewFactory,
    ) {}

    /**
     * Create a new PDF builder from a Blade view.
     *
     * @param  array<string, mixed>  $data
     */
    public function view(string $view, array $data = []): PdfBuilder
    {
        return $this->createBuilder()->view($view, $data);
    }

    /**
     * Create a new PDF builder from raw HTML.
     */
    public function html(string $html): PdfBuilder
    {
        return $this->createBuilder()->html($html);
    }

    /**
     * Create a fresh PDF builder instance.
     */
    public function createBuilder(): PdfBuilder
    {
        return new PdfBuilder($this->renderer, $this->viewFactory);
    }
}
