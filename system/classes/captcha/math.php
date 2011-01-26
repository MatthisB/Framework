<?php

/**
  *
  *  Author:	Matthis
  *  Date:		21.10.2010
  *
  */

namespace Captcha;

class Math extends \Captcha\Controller
{
	private
		$imgClass	= '',
		$imgWidth	= 0,
		$imgHeight	= 0;
		
	public function __construct($imgWidth, $imgHeight, $imgClass = '')
	{
		$id	= parent::generateID();
		parent::__construct($id);
		
		if(!\isValid::Numeric($imgWidth) || !\isValid::Numeric($imgHeight))
		{
			trigger_error('Either the width <i>( '.$imgWidth.' )</i> or the height <i>( '.$imgHeight.' )</i> isn\'t numeric!', E_USER_ERROR);
		}
		
		$this->imgWidth		= $imgWidth;
		$this->imgHeight	= $imgHeight;
	}
	public function createSession($max = 9)
	{
		if(!\isValid::Numeric($max))
		{
			trigger_error('$max <i>( '.$max.' )</i> must be int!', E_USER_ERROR);
		}
				
		$var1	= rand(0, $max);
		$var2	= rand(0, $max);
				
		$this->session->{$this->ID}	= array('width'		=> $this->imgWidth,
											'height'	=> $this->imgHeight,
											'value'		=> $var1.' + '.$var2,
											'result'	=> $var1 + $var2,
											'imgClass'	=> $this->imgClass);
	}
	public function getID()
	{
		return $this->ID;
	}
}
