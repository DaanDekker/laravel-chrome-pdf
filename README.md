# Laravel Chrome PDF

Generate PDFs in Laravel using headless Chrome. Full CSS support including Tailwind CSS, Flexbox, Grid, and modern web features.

## Features

- Full CSS support (Flexbox, Grid, Tailwind v4)
- Real browser rendering engine
- System fonts + Google Fonts
- JavaScript support
- Headers & footers with page numbers
- Queued PDF generation

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Chrome/Chromium installed on the server

## Installation

```bash
composer require daandekker/laravel-chrome-pdf
```

Optionally publish the configuration:

```bash
php artisan vendor:publish --tag=pdf-config
```

## Configuration

Set the Chrome path in your `.env`:

```env
PDF_CHROME_PATH=/usr/bin/chromium
PDF_TIMEOUT=60
PDF_STORAGE_DISK=local
PDF_STORAGE_PATH=pdfs
```

Common Chrome paths:
- **Linux:** `/usr/bin/chromium`, `/usr/bin/chromium-browser`, `/usr/bin/google-chrome`
- **macOS:** `/Applications/Google Chrome.app/Contents/MacOS/Google Chrome`
- **Windows:** `C:\Program Files\Google\Chrome\Application\chrome.exe`
- **Docker (Alpine):** `/usr/bin/chromium-browser`

### Configuration File

```php
// config/pdf.php
return [
    'chrome_path' => env('PDF_CHROME_PATH', '/usr/bin/chromium'),
    'timeout' => env('PDF_TIMEOUT', 60),
    'defaults' => [
        'format' => 'A4',
        'orientation' => 'portrait',
        'margins' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10],
        'print_background' => true,
    ],
    'storage' => [
        'disk' => env('PDF_STORAGE_DISK', 'local'),
        'path' => env('PDF_STORAGE_PATH', 'pdfs'),
    ],
];
```

## Usage

### Basic Usage

```php
use DaanDekker\ChromePdf\Facades\Pdf;

// From a Blade view
$pdf = Pdf::view('pdf.invoice', ['order' => $order]);

// From HTML string
$pdf = Pdf::html('<h1>Hello World</h1>');
```

### Output Methods

```php
// Download as attachment
return Pdf::view('pdf.invoice', $data)->download('invoice.pdf');

// Display inline in browser
return Pdf::view('pdf.invoice', $data)->stream('invoice.pdf');

// Save to file path
Pdf::view('pdf.invoice', $data)->save(storage_path('app/invoices/invoice.pdf'));

// Save to Laravel storage disk
Pdf::view('pdf.invoice', $data)->store('invoices/invoice.pdf', 's3');

// Get raw PDF content
$content = Pdf::view('pdf.invoice', $data)->output();
```

### Page Settings

```php
Pdf::view('pdf.invoice', $data)
    ->format('A4')              // A4, A3, Letter, Legal, Tabloid
    ->orientation('portrait')   // portrait, landscape
    ->landscape()               // Shorthand for landscape
    ->portrait()                // Shorthand for portrait
    ->margins(10, 10, 10, 10)   // top, right, bottom, left (mm)
    ->margin(15)                // Uniform margins (mm)
    ->scale(1.0)                // Scale factor (0.1 - 2.0)
    ->printBackground(true)     // Include CSS backgrounds
    ->download('invoice.pdf');
```

### Headers & Footers

```php
Pdf::view('pdf.invoice', $data)
    ->header('<div style="font-size: 10px; text-align: center;">Company Name</div>')
    ->footer('<div style="font-size: 10px; text-align: center;">Page <span class="pageNumber"></span> of <span class="totalPages"></span></div>')
    ->download('invoice.pdf');
```

Available footer/header variables: `pageNumber`, `totalPages`, `date`, `title`, `url`

### Wait for JavaScript

```php
// Wait for JS to complete (useful for charts/graphs)
Pdf::view('pdf.report', $data)
    ->waitFor(2000)  // milliseconds
    ->download('report.pdf');
```

## Queued Generation

For large PDFs or background processing:

```php
use DaanDekker\ChromePdf\Jobs\GeneratePdf;

GeneratePdf::dispatch(
    view: 'pdf.invoice',
    data: ['order' => $order],
    path: "invoices/order-{$order->id}.pdf",
    disk: 's3'  // optional, defaults to config value
);
```

## Styling Tips

### Using Tailwind CSS

Include Tailwind via CDN in your Blade template:

```html
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-8">
    <h1 class="text-2xl font-bold">Invoice</h1>
    <!-- Your content -->
</body>
</html>
```

### Page Breaks

```html
<!-- Force page break before element -->
<div style="page-break-before: always;">New Page</div>

<!-- Prevent element from breaking across pages -->
<div style="page-break-inside: avoid;">Keep together</div>
```

### Print-Specific Styles

```css
@media print {
    .no-print { display: none; }
    .print-only { display: block; }
}
```

## API Reference

### Pdf Facade Methods

| Method | Description |
|--------|-------------|
| `view(string $view, array $data = [])` | Create PDF from Blade view |
| `html(string $html)` | Create PDF from HTML string |

### PdfBuilder Methods

| Method | Description |
|--------|-------------|
| `format(string $format)` | Set page format (A4, Letter, etc.) |
| `orientation(string $orientation)` | Set orientation (portrait/landscape) |
| `landscape()` | Set landscape orientation |
| `portrait()` | Set portrait orientation |
| `margins(int $top, $right, $bottom, $left)` | Set margins in mm |
| `margin(int $margin)` | Set uniform margins |
| `scale(float $scale)` | Set scale (0.1 - 2.0) |
| `printBackground(bool $print = true)` | Enable/disable CSS backgrounds |
| `header(string $html)` | Set header HTML |
| `footer(string $html)` | Set footer HTML |
| `waitFor(int $ms)` | Wait for JS (milliseconds) |
| `save(string $path)` | Save to file path |
| `store(string $path, ?string $disk)` | Save to storage disk |
| `output()` | Get PDF as string |
| `download(string $filename)` | Return download response |
| `stream(string $filename)` | Return inline response |

## License

MIT License. See [LICENSE](LICENSE) for details.
