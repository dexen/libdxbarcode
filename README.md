# libdxbarcode
_Generate barcodes with this straightforward and small PHP library._

Library in early alpha, API considered unstable.

## Provided functionality
* generating GTIN-13 (aka EAN-13) barcodes
* save as PNG image file
* helpers: check digit computation
* helpers: a command line barcode image file generator
* standards compliance: quiet zone, human readable part
* small library size: one PHP file, one TTF file
* PHP 8.2 compatibility

## Goals v0.1
* emitting directly to web browser
* check digit verification
* helpers: arbitrary scaling of barcode
* clearly defined API (experimental)
* composer.json for Composer

## Goals v0.2
* generating QR Code of Model 2 - Model 40
* 8-bit mode
* standards compliance for QR codes: quiet zone
* utilities: place human readable

## Possible stretch goals for v0.3
* generating QR Code of Model 1
* alphanumeric mode

## Possible stretch goals for v0.4
* generating CODE-128 barcodes

## Goals v1.0
* stable API
* PHP v8.0 - PHP v8.3 compatibility
* optional single-file build
