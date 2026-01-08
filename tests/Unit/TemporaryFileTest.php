<?php

declare(strict_types=1);

use Mixsnoep\PdfPrint\Support\TemporaryFile;

it('creates a temporary file with content', function () {
    $tempFile = new TemporaryFile('<h1>Test</h1>', 'html');

    expect(file_exists($tempFile->path()))->toBeTrue();
    expect(file_get_contents($tempFile->path()))->toBe('<h1>Test</h1>');
});

it('returns correct file url', function () {
    $tempFile = new TemporaryFile('<h1>Test</h1>', 'html');

    expect($tempFile->url())->toStartWith('file://');
    expect($tempFile->url())->toContain($tempFile->path());
});

it('can create from html using static method', function () {
    $tempFile = TemporaryFile::fromHtml('<h1>Test</h1>');

    expect(file_exists($tempFile->path()))->toBeTrue();
    expect($tempFile->path())->toEndWith('.html');
});

it('deletes file on destruct', function () {
    $tempFile = new TemporaryFile('<h1>Test</h1>', 'html');
    $path = $tempFile->path();

    expect(file_exists($path))->toBeTrue();

    unset($tempFile);

    expect(file_exists($path))->toBeFalse();
});

it('can manually delete file', function () {
    $tempFile = new TemporaryFile('<h1>Test</h1>', 'html');
    $path = $tempFile->path();

    expect(file_exists($path))->toBeTrue();

    $tempFile->delete();

    expect(file_exists($path))->toBeFalse();
});

it('handles double delete gracefully', function () {
    $tempFile = new TemporaryFile('<h1>Test</h1>', 'html');

    $tempFile->delete();
    $tempFile->delete(); // Should not throw

    expect(true)->toBeTrue();
});
