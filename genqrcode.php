#!/usr/bin/env rc
<?php

require './dev.php';
require './qrcodelib.php';

$output_pn = $argv[1];
$content = $argv[2];

$Q = new QRimage($content, 'L', 2);
$Q->drawMarkers();
$Q->applyQuietZone();
$Q->upscale(8);

$h = fopen($output_pn, 'w');
$Q->writeToFileHandle($h);
fclose($h);
