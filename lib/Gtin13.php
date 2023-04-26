<?php

namespace dexen\LibDxBarcode;

class MalformedCodeException extends \RuntimeException {}

class Gtin13
{
	protected $input;
	protected $width;
	protected $height;
	protected $font_size;
	protected $human_readable_separation;
	protected $img;
	protected $ca;

	function __construct(string $input, int $height = 64, int $font_size = 10)
	{
		$this->input = $input;
		$this->height = $height;
		$this->width = 6 + 3 + 6*7 + 5 + 6*7 + 3 + 6;
		$this->font_size = $font_size;
		$this->human_readable_separation = 1;
		$this->img = imagecreate($this->width, $this->height);
		$this->setupColors();
		imagefill($this->img, 0, 0, $this->ca['white']);
	}

	protected
	function setupColors()
	{
		$this->ca = [imagecolorallocate($this->img, 255, 255, 255), imagecolorallocate($this->img, 0, 0, 0),];
		[$this->ca['white'],$this->ca['black']] = $this->ca;
		$this->ca['dev0'] = imagecolorallocate($this->img, 200, 0, 0);
		$this->ca['dev1'] = imagecolorallocate($this->img, 0, 200, 0);
		$this->ca['dev2'] = imagecolorallocate($this->img, 0, 0, 200);
	}

	static
	function calculateCheckDigit(string $code)
	{
		if (strlen($code) !== 12)
			throw new MalformedCodeException(sprintf('malformed code: expected exactly 12 digits, got "%d"', strlen($code)));
		$acc = 0;
		foreach (array_reverse(str_split($code)) as $n => $digit) {
			$weight = (($n+1)%2)*2+1;
			$acc += $digit * $weight; }
		return (10-($acc %10))%10;
	}

	function render()
	{
		$this->drawMarkers();
		$this->drawCodeBars();
		$this->drawHumanReadable();
	}

	private
	function drawHumanReadable()
	{
		$color = $this->ca[1];
		$font = __DIR__ .'/' .'fonts/10182_Crystal.ttf';
		$pos = 0; $after_sep = 0;
		$x = 0;
		$y = $this->height;
		imagettftext($this->img, $this->font_size, 0, $x, $y, $color, $font, substr($this->input, 0, 1));

		$pos = 0; $after_sep = 0;
		$x = 6 + 3 + 7*$pos + $after_sep*5;
		$y = $this->height;
		imagettftext($this->img, $this->font_size, 0, $x, $y, $color, $font, substr($this->input, 1, 6));

		$pos = 6; $after_sep = 1;
		$x = 6 + 3 + 7*$pos + $after_sep*5;
		$y = $this->height;
		imagettftext($this->img, $this->font_size, 0, $x, $y, $color, $font, substr($this->input, 7, 6));
	}

		#eor:
		# 0: EVEN
		# 1: ODD
		# 2: RIGHT
	private
	function encoding(int $digit, int $even_odd_right)
	{
		$a = [
			0 => ['0100111','0001101','1110010'],
			1 => ['0110011','0011001','1100110'],
			2 => ['0011011','0010011','1101100'],
			3 => ['0100001','0111101','1000010'],
			4 => ['0011101','0100011','1011100'],
			5 => ['0111001','0110001','1001110'],
			6 => ['0000101','0101111','1010000'],
			7 => ['0010001','0111011','1000100'],
			8 => ['0001001','0110111','1001000'],
			9 => ['0010111','0001011','1110100'],
		];

		return $a[$digit][$even_odd_right];
	}

	protected
	function inputDigit(int $n) : int
	{
		return $this->input[$n];
	}

	private
	function encodingLookupLeft()
	{
		$a = [
			0 => [1,		1,1,1,1,1],
			1 => [1,		1,0,1,0,0],
			2 => [1,		1,0,0,1,0],
			3 => [1,		1,0,0,0,1],
			4 => [1,		0,1,1,0,0],
			5 => [1,		0,0,1,1,0],
			6 => [1,		0,0,0,1,1],
			7 => [1,		0,1,0,1,0],
			8 => [1,		0,1,0,0,1],
			9 => [1,		0,0,1,0,1], ];

		return $a[$this->inputDigit(0)];
	}

	protected
	function drawMarkers()
	{
		$this->drawLead();
		$this->drawSeparator();
		$this->drawTrailer();
	}

	private
	function drawCodeBars()
	{
		$lookup = $this->encodingLookupLeft();
		foreach ([0,1,2,3,4,5,6] as $pos) {
			if ($pos === 0)
				continue;	# the 0th digit is special-handled
			$digit = $this->inputDigit($pos);
			$encoding = $this->encoding($digit, $lookup[$pos-1]);
			$this->drawDigitCode($after_sep=0, $pos-1, $encoding); }
		foreach ([7,8,9,10,11,12] as $pos) {
			$digit = $this->inputDigit($pos);
			$encoding = $this->encoding($digit, 2);
			$this->drawDigitCode($after_sep=1, $pos-1, $encoding); }
	}

	private
	function drawDigitCode(int $after_sep, int $pos, string $encoding)
	{
		$xd = 6 + 3 + 7*$pos + $after_sep*5;
		$color = $this->ca[1];
		static $nn = 0;
		#$color = $this->ca['dev' .$nn];
		$nn = ($nn+1)%3;
		$downto = $this->height - $this->font_size - $this->human_readable_separation;
		foreach (str_split($encoding) as $bar_nr => $has_bar) {
			if ($has_bar)
				imageline($this->img, $xd+$bar_nr, 0, $xd+$bar_nr, $downto, $color);
		}
	}

	protected
	function drawLead()
	{
		$xd = 6 + 0;
		$downto = $this->height - floor($this->font_size/2);
		imageline($this->img, $xd+0, 0, $xd+0, $downto, $this->ca[1]);
		imageline($this->img, $xd+2, 0, $xd+2, $downto, $this->ca[1]);
	}

	protected
	function drawSeparator()
	{
		$xd = 6 + 3+6*7;
		$downto = $this->height - floor($this->font_size/2);
		imageline($this->img, $xd+1, 0, $xd+1, $downto, $this->ca[1]);
		imageline($this->img, $xd+3, 0, $xd+3, $downto, $this->ca[1]);
	}

	protected
	function drawTrailer()
	{
		$xd = $this->width - 3 - 6;
		$downto = $this->height - floor($this->font_size/2);
		imageline($this->img, $xd+0, 0, $xd+0, $downto, $this->ca[1]);
		imageline($this->img, $xd+2, 0, $xd+2, $downto, $this->ca[1]);
	}

	function asImage() { return $this->img; }
}
