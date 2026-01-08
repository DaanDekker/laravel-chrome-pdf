<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint\Process;

final readonly class ProcessResult
{
    public function __construct(
        public int $exitCode,
        public string $stdout,
        public string $stderr,
    ) {}

    public function successful(): bool
    {
        return $this->exitCode === 0;
    }

    public function failed(): bool
    {
        return ! $this->successful();
    }

    public function output(): string
    {
        return $this->stdout;
    }

    public function errorOutput(): string
    {
        return $this->stderr;
    }
}
