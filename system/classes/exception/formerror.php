<?php

/**
  *
  *  Author:	Matthis
  *  Date:		11.09.2010
  *
  */

namespace Exception;

class FormError extends \Exception\a_Exception
{   
	public function getErrorMessage()
	{
		return \Helper\Message::Error($this -> message, '', true);
	}
}
