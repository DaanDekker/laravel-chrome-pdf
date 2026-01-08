<?php

declare(strict_types=1);

use DaanDekker\ChromePdf\Process\Process;
use DaanDekker\ChromePdf\Process\ProcessException;
use DaanDekker\ChromePdf\Process\ProcessResult;

it('can run a simple command', function () {
    $result = Process::fromCommand(['echo', 'hello'])->run();

    expect($result)->toBeInstanceOf(ProcessResult::class);
    expect($result->successful())->toBeTrue();
    expect($result->exitCode)->toBe(0);
    expect(trim($result->stdout))->toBe('hello');
});

it('captures stderr output', function () {
    $result = Process::fromCommand(['php', '-r', 'fwrite(STDERR, "error message");'])->run();

    expect($result->stderr)->toContain('error message');
});

it('returns correct exit code for failed commands', function () {
    $result = Process::fromCommand(['php', '-r', 'exit(42);'])->run();

    expect($result->failed())->toBeTrue();
    expect($result->exitCode)->toBe(42);
});

it('throws exception when using runOrFail on failed command', function () {
    Process::fromCommand(['php', '-r', 'exit(1);'])->runOrFail();
})->throws(ProcessException::class);

it('respects timeout setting', function () {
    Process::fromCommand(['sleep', '10'])
        ->setTimeout(1)
        ->run();
})->throws(ProcessException::class, 'timed out');

it('can set working directory', function () {
    $result = Process::fromCommand(['pwd'])
        ->inDirectory('/tmp')
        ->run();

    expect(trim($result->stdout))->toBe('/tmp');
});
