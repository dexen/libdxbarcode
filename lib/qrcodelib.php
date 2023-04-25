<?php

class ReedSolomonEncoder
{
	function __construct()
	{
	}
}

class QRReedSolomonEncoder
{
	protected $model;
	protected $error_correction_level;
	protected $parameters;
		# X0510, p. 36, Table 9
	const PARAMETERS_LOOKUP = [
		1 => ['L'=>[26,19,2],'M'=>[26,16,4],'Q'=>[26,13,6],'H'=>[26,9,8]],
		2 => ['L'=>[44,34,4],'M'=>[44,28,8],'Q'=>[44,22,11],'H'=>[44,16,14]],
		3 => ['L'=>[70,55,7],'M'=>[70,44,13],'Q'=>[35,17,9],'H'=>[35,13,11]],
# FIXME: add further models
#		4 => ['L'=>[],'M'=>[],'Q'=>[],'H'=>[]],
#...
	];

	function __construct(int $model, string $error_correction_level)
	{
		$this->model = $model;
		$this->error_correction_level = $error_correction_level;
		if (array_key_exists($this->model, static::STANDARD_PARAMETERS)) {
			if (array_key_exists($this->error_correction_level, static::STANDARD_PARAMETERS[$this->model]))
				$this->parameters = static::STANDARD_PARAMETERS[$this->model][$this->arst];
			else
				throw new \Exception(sprintf('unsupported Error Correction Level "%s" for model "%s"', $this->error_correction_level, $this->model)); }
		else
			throw new \Exception(sprintf('unsupported model "%s"', $this->model));
td($this->parameters);
	}
}

class QRimage
{
	protected $input;
	protected $error_correction_level;
	protected $mode;
	protected $model;
	protected $size;
	protected $img;
	protected $ca;

	function __construct(string $input, string $error_correction_level, int $model)
	{
# FIXME - use alphanumeric mode when feasible
		$this->input = $input;
		$this->error_correction_level = $error_correction_level; # L,M,Q,H
		$this->mode = '8 bit byte';
		$this->model = $model;
		$this->size = 17+$model*4;
		$this->img = imagecreate($this->size, $this->size);
		$this->setupColors();
		imagefill($this->img, 0, 0, $this->ca['white']);
	}

	protected
	function setupColors()
	{
		$this->ca = [imagecolorallocate($this->img, 255, 255, 255), imagecolorallocate($this->img, 0, 0, 0),];
		[$this->ca['white'],$this->ca['black']] = $this->ca;
	}

	function drawMarkers()
	{
		$q = $this->size;
		$this->drawPositionDetectionPattern(0, 0);
		$this->drawPositionDetectionPattern($q-7, 0);
		$this->drawPositionDetectionPattern(0, $q-7);
		$this->drawAlignmentPattern($q-7-2, $q-7-2);
		$this->drawTimingPattern($q);
#$this->drawBitstream($this->bitstream());
	}


	protected
	function drawBitstream(string $str)
	{
td(compact('str'));
	}

	protected
	function bitstreamHeader() : string
	{
		if ($this->mode === '8 bit byte') {
			$MODE_8_BIT_BYTE = "\004";
			$M = 4;
			if (($this->model >= 1) && ($this->model <= 9))
				$C = 8;
			else if (($this->model >= 10) && ($this->model <= 26))
				$C = 16;
			else if (($this->model >= 27) && ($this->model <= 40))
				$C = 16;
			else
				throw new \Exception(sprintf('unsupported model "%s"', $this->model));
				# length in bytes
			$D = strlen($this->input);
				# X 0510 p. 25
				# B = M + C + 8D
			$B = $M + $C + 8*$D; }
		else
			throw new \Exception(sprintf('unsupported mode "%s"', $this->mode));
td($B);
		return pack('C', $B);
	}

	protected
	function payloadInMode() : string
	{
		if ($this->mode === '8 bit byte')
			return $this->input;
		else
			throw new \Exception(sprintf('unsupported mode "%s"', $this->mode));
	}

	protected
	function bitstreamTermination() : string
	{
		# default bitstream terminator is '0000' binary, might be shorter as appropriate
		
	}

	protected
	function bitstream() : string
	{
		$ret = '';

		$ret = $this->bitstreamHeader() .$this->payloadInMode() .$this->bitstreamTermination();

#		$ret .= $MODE_8_BIT_BYTE
td($ret);
	}

	protected
	function drawTimingPattern(int $q)
	{
		$b = $this->ca['black'];
		$img = $this->img;

		foreach (range(8, $q-8, $step=2) as $x)
			foreach ([6] as $y)
				imagesetpixel($img, $x, $y, $b);
		foreach ([6] as $x)
			foreach (range(8, $q-8, $step=2) as $y)
				imagesetpixel($img, $x, $y, $b);
	}

	protected
	function drawAlignmentPattern(int $dx, int $dy)
	{
		$b = $this->ca['black'];
		$img = $this->img;

		foreach ([0,1,2,3,4] as $x)
			foreach ([0,4] as $y)
				imagesetpixel($img, $x+$dx, $y+$dy, $b);
		foreach ([0,4] as $x)
			foreach ([1,3] as $y)
				imagesetpixel($img, $x+$dx, $y+$dy, $b);
		foreach ([0,2,4] as $x)
			foreach ([2] as $y)
				imagesetpixel($img, $x+$dx, $y+$dy, $b);
	}

	protected
	function drawPositionDetectionPattern(int $dx, int $dy)
	{
		$b = $this->ca['black'];
		$img = $this->img;

		foreach ([0,6] as $x)
			foreach ([0,1,2,3,4,5,6] as $y)
				imagesetpixel($img, $x+$dx, $y+$dy, $b);
		foreach ([1,5] as $x)
			foreach ([0,6] as $y)
				imagesetpixel($img, $x+$dx, $y+$dy, $b);
		foreach ([2,3,4] as $x)
			foreach ([0,2,3,4,6] as $y)
				imagesetpixel($img, $x+$dx, $y+$dy, $b);
	}

		# FIXME - does this properly handle colors for drawing on the new image?
	function applyQuietZone(int $width = 4)
	{
		if ($width < 4)
			throw new \Exception(sprintf('malformed: standard requires quiet zone of at least 4 modules, got "%s"', $width));
		$size = $this->size;
		$imgold = $this->img;
		$this->img = imagecreate($size+2*$width, $size+2*$width);
		$this->setupColors();
		imagefill($this->img, 0, 0, $this->ca['white']);
		imagecopy($this->img, $imgold, $width, $width, 0, 0, $size, $size);
		imagedestroy($imgold);
	}

	function upscale($factor)
	{
		$size = $this->size;
		$imgold = $this->img;
		$img2 = imagecreate($size*$factor, $size*$factor);
		$this->setupColors();
		imagecopyresized($this->img, $imgold, 0, 0, 0, 0, $size*$factor, $size*$factor, $size, $size);
	}

	function writeToFileHandle($outputH)
	{
		imagepng($this->img, $outputH, $compression_level = 0);
	}
}
