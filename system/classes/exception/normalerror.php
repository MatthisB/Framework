<?php

/**
  *
  *  Author:	Matthis
  *  Date:		08.11.2010
  *
  */

namespace Exception;

class NormalError extends \Exception\a_Exception
{   
	protected
		$miscValues	= array();
		
	public function __construct($message, $miscValues = array(), $code = 0, \Exception $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
		
		$this -> miscValues = $miscValues;
	}
	public function getErrorMessage()
	{
		return \Helper\Message::Error($this -> message, '', true);
	}
	
	public function __get($key)
	{
		if($this->__isset($key))
		{
			return $this->miscValues[$key];
		}
		
		return false;
	}
	public function __isset($key)
	{
		return array_key_exists($key, $this->miscValues);
	}
}
