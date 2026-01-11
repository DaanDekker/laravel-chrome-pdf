<?php

declare(strict_types=1);

use DaanDekker\ChromePdf\Jobs\GeneratePdf;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

it('can be instantiated with required parameters', function () {
    $job = new GeneratePdf(
        view: 'test.view',
        data: ['key' => 'value'],
        path: 'pdfs/test.pdf',
    );

    expect($job->view)->toBe('test.view')
        ->and($job->data)->toBe(['key' => 'value'])
        ->and($job->path)->toBe('pdfs/test.pdf')
        ->and($job->disk)->toBeNull()
        ->and($job->options)->toBe([]);
});

it('can be instantiated with all parameters', function () {
    $job = new GeneratePdf(
        view: 'test.view',
        data: ['key' => 'value'],
        path: 'pdfs/test.pdf',
        disk: 's3',
        options: ['format' => 'A4', 'orientation' => 'landscape'],
    );

    expect($job->view)->toBe('test.view')
        ->and($job->disk)->toBe('s3')
        ->and($job->options)->toBe(['format' => 'A4', 'orientation' => 'landscape']);
});

it('has retry configuration', function () {
    $job = new GeneratePdf(
        view: 'test.view',
        data: [],
        path: 'pdfs/test.pdf',
    );

    expect($job->tries)->toBe(3)
        ->and($job->backoff)->toBe(10);
});

it('uses WithoutOverlapping middleware', function () {
    $job = new GeneratePdf(
        view: 'test.view',
        data: [],
        path: 'pdfs/test.pdf',
    );

    $middleware = $job->middleware();

    expect($middleware)->toHaveCount(1)
        ->and($middleware[0])->toBeInstanceOf(WithoutOverlapping::class);
});

it('has unique id based on path', function () {
    $job = new GeneratePdf(
        view: 'test.view',
        data: [],
        path: 'pdfs/unique-file.pdf',
    );

    expect($job->uniqueId())->toBe('pdfs/unique-file.pdf');
});

it('can be dispatched to queue', function () {
    Queue::fake();

    GeneratePdf::dispatch(
        view: 'test.view',
        data: ['invoice' => 'data'],
        path: 'pdfs/invoice.pdf',
    );

    Queue::assertPushed(GeneratePdf::class, function ($job) {
        return $job->view === 'test.view'
            && $job->path === 'pdfs/invoice.pdf';
    });
});

it('skips generation if file already exists', function () {
    Storage::fake('local');
    Storage::disk('local')->put('pdfs/existing.pdf', 'existing content');

    $job = new GeneratePdf(
        view: 'test.view',
        data: [],
        path: 'pdfs/existing.pdf',
    );

    // Should return early without error
    $job->handle();

    // File should still contain original content
    expect(Storage::disk('local')->get('pdfs/existing.pdf'))->toBe('existing content');
});
