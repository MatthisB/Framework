<?php

/**
 *
 *  Author:	Matthis
 *  Date:		26.06.2010
 *
 */

namespace Log;

class SystemError extends \Log\a_File
{
	public function __construct()
	{
		$this->fileName	= ROOT.'system/files/logs/systemerrors.log';
	}
}
