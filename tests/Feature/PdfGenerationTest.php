<?php

declare(strict_types=1);

use DaanDekker\ChromePdf\Exceptions\ChromeNotFoundException;
use DaanDekker\ChromePdf\Facades\Pdf;
use DaanDekker\ChromePdf\Renderers\ChromeRenderer;

it('throws exception when chrome is not found', function () {
    new ChromeRenderer('/nonexistent/chrome/path');
})->throws(ChromeNotFoundException::class);

it('can create pdf builder from facade', function () {
    // Mock the chrome path to skip validation for this test
    config(['pdf.chrome_path' => '/bin/true']);

    // This will fail at render time but the builder should be created
    $builder = Pdf::html('<h1>Test</h1>');

    expect($builder)->toBeInstanceOf(\DaanDekker\ChromePdf\PdfBuilder::class);
})->skip(fn () => ! file_exists('/bin/true'), 'Requires /bin/true');

it('can create pdf builder from view', function () {
    config(['pdf.chrome_path' => '/bin/true']);

    $builder = Pdf::view('pdf-print::examples.invoice', [
        'invoice' => (object) ['number' => 'INV-001'],
    ]);

    expect($builder)->toBeInstanceOf(\DaanDekker\ChromePdf\PdfBuilder::class);
})->skip(fn () => ! file_exists('/bin/true'), 'Requires /bin/true');
