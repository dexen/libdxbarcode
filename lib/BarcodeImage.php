<?php

namespace dexen\LibDxBarcode;

class BarcodeImage
{
	protected $Barcode;
	protected $img;

	function __construct($Barcode)
	{
		$this->Barcode = $Barcode;
		$this->img = $Barcode->asImage();
	}

	function applyQuietZone() { td('not implemented'); }

	function upscale($factor)
	{
		$imgold = $this->img;
		$width = imagesx($imgold);
		$height = imagesy($imgold);
		$this->img = imagecreate($width*$factor, $height*$factor);
		imagepalettecopy($this->img, $imgold);
		imagecopyresized($this->img, $imgold, 0, 0, 0, 0, $width*$factor, $height*$factor, $width, $height);
	}

	function writeToFileHandle($outputH)
	{
		imagepng($this->img, $outputH, $compression_level = 0);
	}
}
