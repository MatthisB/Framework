<?php

/**
 *
 *  Author:	Matthis
 *  Date:		18.07.2010
 *
 */

namespace Helper;

class Message extends \Helper\HTML
{
	public static function __callstatic($style, $args)
	{
		$template  = new \Template\HTML('helper_message', 'system/files/templates/');
		$template -> class		= 'Message'.ucfirst($style);
		$template -> message	= $args[0];
		$template -> headLine	= (isset($args[1]) && !empty($args[1]) ? $args[1] : '');

		if(isset($args[2]) && $args[2] == true)
		{
			return $template -> returnTemplate();
		}

		$template -> printTemplate();
	}
}
