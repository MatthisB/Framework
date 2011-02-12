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
	/**
	 * returns 'real' cookie ( $_COOKIE ) if set, otherwise false
	 * 
	 * @param	mixed
	 */
	public static function getCookie($name)
	{
		$name = \Filter::systemID($name);
		if(!self::exists($name))
		{
			return false;
		}
		return $_COOKIE[$name];
	}
	/**
	 * wrapper for php intern function: setcookie
	 * 
	 * @param	string	$name
	 * @param	string	$value
	 * @param	int		$expire
	 * @param	string	$path
	 * @param	string	$domain
	 * @param	bool	$secure
	 * @param	bool	$httponly
	 */
	public static function setCookie($name, $value, $expire = 0, $path = '/', $domain = '', $secure = false, $httponly = true)
	{
		$name = \Filter::systemID($name);
		setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
	}
	/**
	 * deletes a 'real' cookie from browser
	 * 
	 * @param	string	$name
	 */
	public static function delCookie($name)
	{
		$name = \Filter::systemID($name);
		self::setCookie($name, '', (time()-1337));
	}
	/**
	 * checks if 'real' cookie exists
	 * 
	 * @param	string	$name
	 */
	public static function exists($name)
	{
		$name = \Filter::systemID($name);
		return (array_key_exists($name, $_COOKIE) ? true : false);
	}
}
