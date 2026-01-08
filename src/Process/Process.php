<?php

declare(strict_types=1);

namespace Mixsnoep\PdfPrint\Process;

final class Process
{
    /** @var array<int, string> */
    private array $command;

    private ?string $cwd = null;

    /** @var array<string, string> */
    private array $env = [];

    private int $timeout = 60;

    /**
     * @param  array<int, string>  $command
     */
    public function __construct(array $command)
    {
        $this->command = $command;
    }

    /**
     * @param  array<int, string>  $command
     */
    public static function fromCommand(array $command): static
    {
        return new static($command);
    }

    public function inDirectory(string $cwd): static
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * @param  array<string, string>  $env
     */
    public function withEnvironment(array $env): static
    {
        $this->env = $env;

        return $this;
    }

    public function setTimeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    public function run(): ProcessResult
    {
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $env = $this->env !== [] ? $this->env : null;

        $process = proc_open(
            $this->command,
            $descriptors,
            $pipes,
            $this->cwd,
            $env
        );

        if (! is_resource($process)) {
            throw ProcessException::failedToStart(implode(' ', $this->command));
        }

        fclose($pipes[0]);

        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $stdout = '';
        $stderr = '';
        $startTime = time();

        while (true) {
            $status = proc_get_status($process);

            if (! $status['running']) {
                $stdout .= stream_get_contents($pipes[1]);
                $stderr .= stream_get_contents($pipes[2]);
                break;
            }

            if ((time() - $startTime) > $this->timeout) {
                proc_terminate($process, 9); // SIGKILL
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);

                throw ProcessException::timedOut($this->timeout);
            }

            $stdout .= fread($pipes[1], 8192) ?: '';
            $stderr .= fread($pipes[2], 8192) ?: '';

            usleep(10000);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if (isset($status['exitcode']) && $status['exitcode'] !== -1) {
            $exitCode = $status['exitcode'];
        }

        return new ProcessResult($exitCode, $stdout, $stderr);
    }

    public function runOrFail(): ProcessResult
    {
        $result = $this->run();

        if ($result->failed()) {
            throw ProcessException::failed($result);
        }

        return $result;
    }
}
