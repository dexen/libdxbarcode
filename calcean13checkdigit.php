#!/usr/bin/env rc
<?php

require './dev.php';
require './lib/Gtin13.php';

use dexen\LibDxBarcode\Gtin13;

$code = $argv[1];

echo Gtin13::calculateCheckDigit($code);
