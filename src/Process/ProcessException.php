<?php

declare(strict_types=1);

namespace DaanDekker\ChromePdf\Process;

use Exception;

class ProcessException extends Exception
{
    public static function failedToStart(string $command): static
    {
        return new static("Failed to start process: {$command}");
    }

    public static function timedOut(int $timeout): static
    {
        return new static("Process timed out after {$timeout} seconds");
    }

    public static function failed(ProcessResult $result): static
    {
        $message = "Process failed with exit code {$result->exitCode}";

        if ($result->stderr !== '') {
            $message .= ": {$result->stderr}";
        }

        return new static($message);
    }
}
