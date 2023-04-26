#!/usr/bin/env rc
<?php

require './dev.php';
require './lib/Gtin13.php';
require './lib/BarcodeImage.php';

$output_pn = $argv[1];
$content = $argv[2];

$Q = new dexen\LibDxBarcode\Gtin13($content);
$Q->render();

$BI = new dexen\LibDxBarcode\BarcodeImage($Q);
$BI->upscale(8);
$h = fopen($output_pn, 'w');
$BI->writeToFileHandle($h);
fclose($h);
