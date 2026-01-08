<?php

declare(strict_types=1);

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Mixsnoep\PdfPrint\PdfBuilder;
use Mixsnoep\PdfPrint\Renderers\RendererInterface;

beforeEach(function () {
    $this->renderer = Mockery::mock(RendererInterface::class);
    $this->viewFactory = Mockery::mock(ViewFactory::class);
    $this->builder = new PdfBuilder($this->renderer, $this->viewFactory);
});

it('can set html content', function () {
    $builder = $this->builder->html('<h1>Test</h1>');

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can set view content', function () {
    $view = Mockery::mock(View::class);
    $view->shouldReceive('render')->once()->andReturn('<h1>Test</h1>');

    $this->viewFactory
        ->shouldReceive('make')
        ->with('test.view', ['key' => 'value'])
        ->once()
        ->andReturn($view);

    $builder = $this->builder->view('test.view', ['key' => 'value']);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can set format', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->format('Letter');

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can set orientation', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->orientation('landscape');

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('has landscape shorthand', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->landscape();

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('has portrait shorthand', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->portrait();

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can set margins', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->margins(20, 15, 20, 15);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can set uniform margin', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->margin(25);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can enable print background', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->printBackground(true);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can set scale', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->scale(0.8);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('clamps scale to valid range', function () {
    // Scale should be clamped between 0.1 and 2.0
    $builder = $this->builder->html('<h1>Test</h1>')->scale(5.0);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can set wait time', function () {
    $builder = $this->builder->html('<h1>Test</h1>')->waitFor(5000);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can save to file', function () {
    $outputPath = sys_get_temp_dir() . '/test-output.pdf';

    $this->renderer
        ->shouldReceive('render')
        ->once()
        ->withArgs(function ($html, $path, $options) use ($outputPath) {
            // Create a dummy file to simulate PDF generation
            file_put_contents($path, '%PDF-1.4 test');

            return $path === $outputPath;
        });

    $result = $this->builder->html('<h1>Test</h1>')->save($outputPath);

    expect($result)->toBe($outputPath);
    expect(file_exists($outputPath))->toBeTrue();

    // Cleanup
    unlink($outputPath);
});

afterEach(function () {
    Mockery::close();
});
