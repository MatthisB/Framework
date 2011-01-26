<?php

/**
 *
 *  Author:	Matthis
 *  Date:		07.06.2010
 *
 */

namespace Header;

class Request
{
	/**
	 * Entfernt falls magic_quotes aktiviert sind die backslashes
	 *
	 * @return void
	 */
	public static function removeMagicQuotes()
	{
		if(get_magic_quotes_gpc())
		{
			$_GET		= self::stripSlashesDeep($_GET);
			$_POST		= self::stripSlashesDeep($_POST);
			$_COOKIE	= self::stripSlashesDeep($_COOKIE);
		}
	}

	/**
	 * Die ausführende Funktion von removeMagicQuotes()
	 *
	 * @param	$value
	 * @return	$value
	 */
	public static function stripSlashesDeep($value)
	{
		$value = is_array($value) ? array_map(array('self', 'stripSlashesDeep'), $value) : stripslashes($value);
		return $value;
	}

	public static function isAjax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}

	public static function getMethod()
	{
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}
	public static function getURI()
	{
		return \Helper\HTML::EncodeEntities($_SERVER['REQUEST_URI']);
	}
	public static function getHeader($header)
	{
		$header = strtoupper($header);

		if(substr($header, 0, 5) !== 'HTTP_')
		{
			return '';
		}

		return (isset($_SERVER[$header]) ? $_SERVER[$header] : '');
	}

	public static function getReferer()
	{
		return \Helper\HTML::EncodeEntities(self::getHeader('HTTP_REFERER'));
	}
	public static function getUserAgent()
	{
		return self::getHeader('HTTP_USER_AGENT');
	}
	public static function getAcceptFileTypes()
	{
		return self::getHeader('HTTP_ACCEPT');
	}
	public static function getAcceptLanguage()
	{
		return self::getHeader('HTTP_ACCEPT_LANGUAGE');
	}
	public static function getAcceptEncoding()
	{
		return self::getHeader('HTTP_ACCEPT_ENCODING');
	}
	public static function getAcceptCharset()
	{
		return self::getHeader('HTTP_ACCEPT_CHARSET');
	}
}
