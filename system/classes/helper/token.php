<?php

/**
  *
  *  Author:	Matthis
  *  Date:		13.09.2010
  *
  */

namespace Helper;

class Token extends \a_Singleton
{
	protected function __construct()
	{
		if(!isset(\Session\Scope::Instance()->_token))
		{
			\Session\Scope::Instance()->_token = new \ArrayObject();
		}
	}
	
	public static function createHash()
	{
		$path		= \Helper\URL::$_CURRENT;
		$timestamp	= microtime();
		$hash		= self::generateHash($path, $timestamp);
		
		\Session\Scope::Instance()->_token[\Helper\URL::$_CURRENT] = array($hash, $timestamp);
		
		return $hash;
	}
	public static function checkHash()
	{
		$referer	= \Header\Request::getReferer();
		
		if(!array_key_exists($referer, \Session\Scope::Instance()->_token))
		{
			return false;
		}
		if(\Session\Scope::Instance()->_token[$referer][0] == self::generateHash($referer, \Session\Scope::Instance()->_token[$referer][1]))
		{
			unset(\Session\Scope::Instance()->_token[$referer]);
			return true;
		}
		return false;
	}
	
	private static function generateHash($path, $timestamp)
	{
		return md5($timestamp.$path.\Helper\User::generateFingerprint());
	}
}
