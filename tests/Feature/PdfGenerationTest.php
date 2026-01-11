<?php

declare(strict_types=1);

use DaanDekker\ChromePdf\Exceptions\ChromeNotFoundException;
use DaanDekker\ChromePdf\Facades\Pdf;
use DaanDekker\ChromePdf\PdfBuilder;
use DaanDekker\ChromePdf\Renderers\ChromeRenderer;

it('throws exception when chrome is not found on render', function () {
    $renderer = new ChromeRenderer('/nonexistent/chrome/path');
    $renderer->render('<h1>Test</h1>', '/tmp/test.pdf');
})->throws(ChromeNotFoundException::class);

it('can create pdf builder from facade', function () {
    $builder = Pdf::html('<h1>Test</h1>');

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});

it('can create pdf builder from view', function () {
    app('view')->addNamespace('test', sys_get_temp_dir());
    file_put_contents(sys_get_temp_dir() . '/simple.blade.php', '<h1>{{ $title }}</h1>');

    $builder = Pdf::view('test::simple', ['title' => 'Test']);

    expect($builder)->toBeInstanceOf(PdfBuilder::class);
});
