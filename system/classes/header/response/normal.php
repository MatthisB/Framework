<?php

/**
 *
 *  Author:	Matthis
 *  Date:		29.05.2010
 *
 */

namespace Header\Response;

class Normal extends \Header\Response
{
	public function __construct()
	{
		ob_start();
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
