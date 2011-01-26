<?php

/**
 *
 *  Author:	Matthis
 *  Date:		15.06.2010
 *
 */

namespace Cookie;

class Real
{
	public static function getCookie($name)
	{
		$name = \Filter::systemID($name);
		if(!self::exists($name))
		{
			return false;
		}
		return $_COOKIE[$name];
	}
	public static function setCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true)
	{
		$name = \Filter::systemID($name);
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}
	public static function delCookie($name)
	{
		$name = \Filter::systemID($name);
		self::setCookie($name, '', (time()-1337));
	}
	public static function exists($name)
	{
		$name = \Filter::systemID($name);
		return (array_key_exists($name, $_COOKIE) ? true : false);
	}
}
