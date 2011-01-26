<?php

/**
  *
  *  Author:	Matthis
  *  Date:		10.10.2010
  *
  */

namespace Helper;

class InputObject
{
	const
		GET			= 'GET',
		POST		= 'POST',
		REQUEST		= 'REQUEST';
		
	protected
		$varArray	= '';
	
	public function __construct($varName = \Helper\InputObject::GET)
	{
		if(!\isValid::VarName($varName))
		{
			trigger_error('InputObject varName <i>( '.$varName.' )</i> isn\'t valid!', E_USER_ERROR);
		}
		
		switch($varName)
		{
			case self::GET:
				$this -> varArray	= &$_GET;
				break;
				
			case self::POST:
				$this -> varArray	= &$_POST;
				break;
				
			case self::REQUEST:
				$this -> varArray	= &$_REQUEST;
				break;
				
			default:
				trigger_error('InputObject varName <i>( '.$varName.' )</i> doesn\'t exist!', E_USER_ERROR);
				break;
		}
	}
	public function __isset($key)
	{
		return array_key_exists($key, $this->varArray);
	}
	public function __unset($key)
	{
		unset($this->varArray[$key]);
	}
	public function __set($key, $value)
	{
		$this->varArray[$key] = $value;
	}
	public function __get($key)
	{
		if(!$this->__isset($key))
		{
			return false;
		}
		if(is_array($this->varArray[$key]))
		{
			return (object) $this->varArray[$key];
		}
		return $this->varArray[$key];
	}
	public function __tostring()
	{
		print_r($this->varArray);
	}
	public function __invoke()
	{
		return $this->varArray;
	}
}
