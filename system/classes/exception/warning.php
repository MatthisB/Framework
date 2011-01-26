<?php

/**
 *
 *  Author:	Matthis
 *  Date:		22.07.2010
 *
 */

namespace Exception;

class Warning extends \Exception\a_Exception
{
	public function getMessage()
	{
		return \Helper\Message::Notice($this -> message, '', true);
	}
}

