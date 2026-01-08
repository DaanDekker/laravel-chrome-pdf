<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Storage;
use Mixsnoep\PdfPrint\Facades\Pdf;

class GeneratePdf implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 10;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $view,
        public array $data,
        public string $path,
        public ?string $disk = null,
        public array $options = [],
    ) {}

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->path))->dontRelease(),
        ];
    }

    public function handle(): void
    {
        $storage = $this->disk
            ? Storage::disk($this->disk)
            : Storage::disk(config('pdf.storage.disk', 'local'));

        if ($storage->exists($this->path)) {
            return;
        }

        $builder = Pdf::view($this->view, $this->data);

        if (isset($this->options['format'])) {
            $builder->format($this->options['format']);
        }

        if (isset($this->options['orientation'])) {
            $builder->orientation($this->options['orientation']);
        }

        if (isset($this->options['margins'])) {
            $builder->margins(
                $this->options['margins']['top'] ?? 10,
                $this->options['margins']['right'] ?? 10,
                $this->options['margins']['bottom'] ?? 10,
                $this->options['margins']['left'] ?? 10,
            );
        }

        if (isset($this->options['print_background'])) {
            $builder->printBackground($this->options['print_background']);
        }

        $builder->store($this->path, $this->disk);
    }

    /**
     * Get the unique ID for preventing overlapping jobs.
     */
    public function uniqueId(): string
    {
        return $this->path;
    }
}
