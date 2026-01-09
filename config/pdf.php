<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chrome/Chromium Executable Path
    |--------------------------------------------------------------------------
    |
    | The path to the Chrome or Chromium executable. Common paths:
    | - Linux: /usr/bin/chromium, /usr/bin/chromium-browser, /usr/bin/google-chrome
    | - macOS: /Applications/Google Chrome.app/Contents/MacOS/Google Chrome
    | - Windows: C:\Program Files\Google\Chrome\Application\chrome.exe
    |
    */
    'chrome_path' => env('PDF_CHROME_PATH', '/usr/bin/chromium'),

    /*
    |--------------------------------------------------------------------------
    | Process Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for Chrome to render a PDF.
    |
    */
    'timeout' => env('PDF_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Storage Settings (for queued generation)
    |--------------------------------------------------------------------------
    |
    | Default disk and path for storing generated PDFs when using queued jobs.
    |
    */
    'storage' => [
        'disk' => env('PDF_STORAGE_DISK', 'local'),
        'path' => env('PDF_STORAGE_PATH', 'pdfs'),
    ],
];
