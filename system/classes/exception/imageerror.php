<?php

/**
  *
  *  Author:	Matthis
  *  Date:		19.10.2010
  *
  */

namespace Exception;

class ImageError extends \Exception\a_Exception
{
	public function setHeader()
	{
		\Registry::Instance()->Header->addHeader('Content-Type', 'image/png');
	}
	public function printErrorPicture()
	{
		$fontSize	= 10;
		$fontFace	= ROOT.'system/files/fonts/persans.ttf';		
		$chars		= strlen($this->message);
		$width		= $fontSize * ($chars + 2);	# Zeichen * ( Zeichen + Padding )
		$height		= $fontSize * 2;
		
		$img		= imagecreatetruecolor($width, $height);
		$backColor	= imagecolorallocate($img, 255, 255, 255);
		$fontColor	= imagecolorallocate($img, 0, 0, 0);
		
		imagefill($img, 0, 0, $backColor);
		
		$x	= ($width / 2 - $chars * ($fontSize / 2));
		$y	= ($height / 2 + $fontSize / 2);
		
		for($i = 1; $i <= $chars; $i++)
		{
			imagettftext($img, $fontSize, 0, $x, $y, $fontColor, $fontFace, $this->message{$i-1});
			
			$x += $fontSize;
		}
		
		imagepng($img);
		imagedestroy($img);
	}
}
