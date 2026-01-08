<?php

declare(strict_types=1);

namespace DaanDekker\ChromePdf;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use DaanDekker\ChromePdf\Renderers\RendererInterface;
use DaanDekker\ChromePdf\Support\TemporaryFile;

final class PdfBuilder
{
    private string $html;

    private string $format = 'A4';

    private string $orientation = 'portrait';

    /** @var array{top: int, right: int, bottom: int, left: int} */
    private array $margins = [
        'top' => 10,
        'right' => 10,
        'bottom' => 10,
        'left' => 10,
    ];

    private bool $printBackground = true;

    private ?string $header = null;

    private ?string $footer = null;

    private ?int $wait = null;

    private float $scale = 1.0;

    public function __construct(
        private readonly RendererInterface $renderer,
        private readonly ViewFactory $viewFactory,
    ) {}

    /**
     * Set the HTML content directly.
     */
    public function html(string $html): static
    {
        $this->html = $this->wrapWithPageStyles($html);

        return $this;
    }

    /**
     * Set the content from a Blade view.
     *
     * @param  array<string, mixed>  $data
     */
    public function view(string $view, array $data = []): static
    {
        $html = $this->viewFactory->make($view, $data)->render();

        return $this->html($html);
    }

    /**
     * Set the page format (A4, Letter, Legal, etc.).
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Set the page orientation (portrait or landscape).
     */
    public function orientation(string $orientation): static
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Shorthand for landscape orientation.
     */
    public function landscape(): static
    {
        return $this->orientation('landscape');
    }

    /**
     * Shorthand for portrait orientation.
     */
    public function portrait(): static
    {
        return $this->orientation('portrait');
    }

    /**
     * Set page margins in millimeters.
     */
    public function margins(int $top, int $right, int $bottom, int $left): static
    {
        $this->margins = compact('top', 'right', 'bottom', 'left');

        return $this;
    }

    /**
     * Set uniform margins on all sides.
     */
    public function margin(int $margin): static
    {
        return $this->margins($margin, $margin, $margin, $margin);
    }

    /**
     * Enable or disable printing of CSS backgrounds.
     */
    public function printBackground(bool $print = true): static
    {
        $this->printBackground = $print;

        return $this;
    }

    /**
     * Set a custom header HTML.
     */
    public function header(string $html): static
    {
        $this->header = $html;

        return $this;
    }

    /**
     * Set a custom footer HTML.
     */
    public function footer(string $html): static
    {
        $this->footer = $html;

        return $this;
    }

    /**
     * Wait for JavaScript to complete (milliseconds).
     */
    public function waitFor(int $milliseconds): static
    {
        $this->wait = $milliseconds;

        return $this;
    }

    /**
     * Set the scale factor (0.1 to 2.0).
     */
    public function scale(float $scale): static
    {
        $this->scale = max(0.1, min(2.0, $scale));

        return $this;
    }

    /**
     * Save the PDF to a file path.
     */
    public function save(string $path): string
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $this->renderer->render($this->html, $path, $this->getOptions());

        return $path;
    }

    /**
     * Save the PDF to a storage disk.
     */
    public function store(string $path, ?string $disk = null): string
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.pdf';

        $this->save($tempPath);

        try {
            $storage = $disk ? Storage::disk($disk) : Storage::disk();
            $storage->put($path, file_get_contents($tempPath));

            return $path;
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Get the PDF content as a string.
     */
    public function output(): string
    {
        $tempPath = sys_get_temp_dir() . '/' . uniqid('pdf_', true) . '.pdf';

        try {
            $this->save($tempPath);

            return file_get_contents($tempPath);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Return a download response.
     */
    public function download(string $filename = 'document.pdf'): Response
    {
        return new Response($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Return a response that displays the PDF inline.
     */
    public function stream(string $filename = 'document.pdf'): Response
    {
        return new Response($this->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get the renderer options array.
     *
     * @return array<string, mixed>
     */
    private function getOptions(): array
    {
        $options = [
            'format' => $this->format,
            'orientation' => $this->orientation,
            'margins' => $this->margins,
            'print_background' => $this->printBackground,
            'scale' => $this->scale,
        ];

        if ($this->header !== null) {
            $options['header'] = $this->header;
        }

        if ($this->footer !== null) {
            $options['footer'] = $this->footer;
        }

        if ($this->wait !== null) {
            $options['wait'] = $this->wait;
        }

        return $options;
    }

    /**
     * Wrap HTML with @page CSS styles for margins and format.
     */
    private function wrapWithPageStyles(string $html): string
    {
        $pageSize = strtolower($this->format);
        $orientation = $this->orientation === 'landscape' ? ' landscape' : '';

        $margins = sprintf(
            '%dmm %dmm %dmm %dmm',
            $this->margins['top'],
            $this->margins['right'],
            $this->margins['bottom'],
            $this->margins['left']
        );

        $pageStyle = <<<CSS
        <style>
            @page {
                size: {$pageSize}{$orientation};
                margin: {$margins};
            }
            @media print {
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            }
        </style>
        CSS;

        if (stripos($html, '</head>') !== false) {
            return str_ireplace('</head>', $pageStyle . '</head>', $html);
        }

        return $pageStyle . $html;
    }
}
