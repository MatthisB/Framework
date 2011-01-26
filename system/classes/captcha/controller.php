<?php

/**
  *
  *  Author:	Matthis
  *  Date:		18.10.2010
  *
  */

namespace Captcha;

class Controller
{
	protected
		$ID			= '',
		$session	= NULL;
		
	public function __construct($ID)
	{
		if(!isset(\Session\Scope::Instance()->_captcha))
		{
			\Session\Scope::Instance()->_captcha = new \ArrayObject();
		}
		$this->session = &\Session\Scope::Instance()->_captcha;
		
		$this->ID		= \Filter::systemID($ID);
	}
	public function sessionExists()
	{
		return isset($this->session->{$this->ID});
	}
	public function checkSession($value)
	{
		if(!$this->sessionExists())
		{
			return false;
		}
		
		return ($value == $this->getParameter()->result);
	}
	public function deleteSession()
	{
		if($this->sessionExists())
		{
			unset($this->session->{$this->ID});
		}
	}
	public function getParameter()
	{
		if(!$this->sessionExists())
		{
			trigger_error('Captcha wasn\'t initialized!', E_USER_WARNING);
			return false;
		}
		
		return (object) $this->session->{$this->ID};
	}
	
	public static function generateID()
	{
		return \Helper\String::random(\Helper\String::UNIQUE, 10);
	}
}
