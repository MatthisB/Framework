<?php

/**
  *
  *  Author:	Matthis
  *  Date:		18.10.2010
  *
  */

namespace Captcha;

class Image
{
	protected
		$img		= NULL,
		
		$fontFile	= '',
		$fontSize	= 20,
		$width		= 0,
		$height		= 0,
		$lines		= false,
		$border		= false,
		
		$value		= 'ERROR';
	
	/**
	 * initialise the captcha image
	 * 
	 * @param	string	$value
	 * @param	int		$width
	 * @param	int		$height
	 */
	public function __construct($value, $width, $height)
	{
		if(!\isValid::string($value))
		{
			throw new \Exception\ImageError('The value <i>( '.$value.' )</i> must be a string!');
		}
		$this->value = $value;

		if(!\isValid::Numeric($width) || !\isValid::Numeric($height))
		{
			throw new \Exception\ImageError('Either the width <i>( '.$width.' )</i> or the height <i>( '.$height.' )</i> isn\'t numeric!');
		}
		$this->width  = (int) $width;
		$this->height = (int) $height;
		
		$this->setFont('Alanden_.ttf');
	}
	/**
	 * sets the font for the text
	 * 
	 * @param	string	$fontFile
	 */
	public function setFont($fontFile)
	{
		if(!is_readable(ROOT.'system/files/fonts/'.$fontFile))
		{
			throw new \Exception\ImageError('Could not find Font-File <i>( system/files/fonts/'.$fontFile .' )</i>!');	
		}
		
		$this->fontFile = ROOT.'system/files/fonts/'.$fontFile;
	}
	/**
	 * sets the font size for the text
	 * 
	 * @param	int		$fontSize
	 */
	public function setFontSize($fontSize)
	{
		if(!\isValid::Numeric($fontSize))
		{
			throw new \Exception\ImageError('Font-Size <i>( '.$fontSize.' )</i> isn\'t numeric!');
		}
		
		$this->fontSize = (int) $fontSize;
	}
	/**
	 * if you want a border set $border = true
	 * 
	 * @param	bool	$border
	 */
	public function setBorder($border)
	{
		if($border == true)
		{
			$this->border = true;
		}
	}
	/**
	 * if you want some hacker-irritating lines on the image
	 *
	 * @param	bool	$lines
	 */
	public function setLines($lines)
	{
		if($lines == true)
		{
			$this->lines = true;
		}
	}
	/**
	 * finally, create the captcha image
	 */
	public function createCaptcha()
	{
		$this->img	= imagecreatetruecolor($this->width, $this->height);
		imagefill($this->img, 0, 0, $this->generateColor('bright'));
		
		$chars	= strlen($this->value)-1;
		$width	= ($this->width / 2 - $chars * 10);
		$height	= ($this->height / 2 + $this->fontSize / 2);
		
		if($this->lines == true)
		{
			$this->createLines();
		}
		if($this->border == true)
		{
			$this->createBorder();
		}
		
		for($i = 0; $i <= $chars; $i++)
		{
			$angle = (bool) rand(0, 1);
			$angle = ($angle ? rand(0, 25) : rand(0, -25));
			
			imagettftext($this->img, $this->fontSize, $angle, $width, $height, $this->generateColor(), $this->fontFile, $this->value{$i});
			
			$width += $this->fontSize;
		}
		
		imagepng($this->img);
		imagedestroy($this->img);
	}
	
	/**
	 * returns some prefabricated colors
	 * 
	 * @param	string	$style
	 */
	private function generateColor($style = 'random')
	{
		switch(strtolower($style))
		{
			case 'bright':
				$r		= rand(200, 255);
				$g		= rand(200, 255);
				$b		= rand(200, 255);
				break;
				
			case 'verybright':
				$r		= rand(230, 255);
				$g		= rand(230, 255);
				$b		= rand(230, 255);
				break;
			
			case 'black':
				$r		= 0;
				$g		= 0;
				$b		= 0;
				break;
			
			case 'white':
				$r		= 255;
				$g		= 255;
				$b		= 255;
				break;
			
			case 'grey':
				$r		= 200;
				$g		= 200;
				$b		= 200;
				break;
			
			default:
				$r		= rand(0, 255);
				$g		= rand(0, 255);
				$b		= rand(0, 255);
				break;
		}
		
		return imagecolorallocate($this->img, $r, $g, $b);
	}
	/**
	 * creates the border around the image
	 */
	private function createBorder()
	{
		$x = $this->width;
		$y = $this->height;

		$color = $this->generateColor('white');
		imageline($this->img,    1,    1,   $x,    1, $color);
		imageline($this->img,    0, $y-2,   $x, $y-2, $color);
		imageline($this->img,    1,    1,    1, $y-2, $color);
		imageline($this->img, $x-2,    0, $x-2, $y-2, $color);

		$color = $this->generateColor('grey');
		imageline($this->img,    0,    0,   $x,    0, $color);
		imageline($this->img,    0, $y-1,   $x, $y-1, $color);
		imageline($this->img,    0,    0,    0, $y-1, $color);
		imageline($this->img, $x-1,    0, $x-1, $y-1, $color);
	}
	/**
	 * creates the lines through the image
	 */
	private function createLines()
	{
		if($this->lines == true)
		{
			for($i = rand()%30; $i < $this->width; $i += rand()%30)
			{
				imageline($this->img, $i, $this->width, $i, 0, $this->generateColor('veryBright'));
			}
			for($i = rand()%30; $i < $this->height; $i += rand()%30)
			{
				imageline($this->img, $this->width, $i, 0, $i, $this->generateColor('veryBright'));
			}
		}
	}
}
