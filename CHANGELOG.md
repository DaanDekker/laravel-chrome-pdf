# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-11

### Added
- Initial release
- PDF generation from Blade views and raw HTML
- Full CSS support including Flexbox, Grid, and Tailwind CSS v4
- Output methods: `download()`, `stream()`, `save()`, `store()`, `output()`
- Page configuration: format, orientation, margins, scale, printBackground
- Custom headers and footers with page number placeholders
- JavaScript wait option for dynamic content
- Queued PDF generation with `GeneratePdf` job
- Configurable Chrome/Chromium path and timeout
- Laravel 11 and 12 support
- PHP 8.2, 8.3, and 8.4 support
