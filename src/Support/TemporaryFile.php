<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint\Support;

final class TemporaryFile
{
    private string $path;

    private bool $deleted = false;

    public function __construct(string $content, string $extension = 'html')
    {
        $this->path = sys_get_temp_dir() . '/' . uniqid('pdf_print_', true) . '.' . $extension;

        file_put_contents($this->path, $content);
    }

    public static function fromHtml(string $html): static
    {
        return new static($html, 'html');
    }

    public function path(): string
    {
        return $this->path;
    }

    public function url(): string
    {
        return 'file://' . $this->path;
    }

    public function delete(): void
    {
        if (! $this->deleted && file_exists($this->path)) {
            unlink($this->path);
            $this->deleted = true;
        }
    }

    public function __destruct()
    {
        $this->delete();
    }
}
