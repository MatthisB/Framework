<?php

/**
 *
 *  Author:	Matthis
 *  Date:		29.05.2010
 *
 */

namespace Header\Response;

class GZip extends \Header\Response
{
	public function __construct()
	{
		$accept = \Header\Request::getAcceptEncoding();
		$accept = explode(',', $accept);

		if(in_array('gzip', $accept))
		{
			ob_start('ob_gzhandler');
		}
		else
		{
			ob_start();
		}
	}
	public function getContents()
	{
		return ob_get_contents();
	}
	public function endBuffer()
	{
		ob_end_flush();
	}
}
