<?php

/**
 *
 *  Author:	Matthis
 *  Date:		18.06.2010
 *
 */

namespace Helper;

class URL extends \a_Singleton
{
	public static
		$_SITEPATH	= '',
		$_CURRENT	= '';

	private
		$_fragments	= array();

	protected function __construct()
	{
		$route				= (isEmpty('route') ? '' : $_GET['route']);
		$route				= urldecode($route);

		$this->_fragments	= explode('/', $route);
		$this->_fragments	= array_map('\Helper\HTML::EncodeEntities', $this->_fragments);

		self::$_SITEPATH	= \Registry::Instance()->frameworkConfig->sitePath;
		self::$_CURRENT		= self::$_SITEPATH . implode('/', $this->_fragments);
	}
	public function __get($key)
	{
		switch($key)
		{
			case '_class':	return ($this->__isset(0) ? $this->_fragments[0] : 'NULL');
			case '_method':	return ($this->__isset(1) ? $this->_fragments[1] : 'NULL');
				
			default:
				{
					if(preg_match('/_([0-9]+)/', $key))
					{
						$key = substr($key, 1);
					}
					if($this->__isset($key))
					{
						return $this->_fragments[$key];
					}
					return 'NULL';
				}
		}
	}
	public function __isset($key)
	{
		if(preg_match('/_([0-9]+)/', $key))
		{
			$key = substr($key, 1);
		}
		return (array_key_exists($key, $this->_fragments) && !empty($this->_fragments[$key]));
	}

	public static function stripScheme($uri)
	{
		if(stripos($uri, '://') !== false)
		{
			$uri = explode('://', $uri, 2);
			return $uri[1];
		}
		return $uri;
	}
	public static function formatURL(array $pieces)
	{
		$url  = \Registry::Instance()->frameworkConfig->sitePath;
		$url .= implode('/', $pieces);
		$url .= '/';

		$url  = preg_replace('/\/+/', '/', $url);

		return $url;
	}
	public static function convertToURL($string, $delimiter = '_', $caseSensitive = 0)
	{
		switch($caseSensitive)
		{
			case 1:
				$string = strtolower($string);
				break;
			case 2:
				$string = strtoupper($string);
				break;
		}

		$string = str_replace(array('ä',  'Ä',  'ö',  'Ö',  'ü',  'Ü',  'ß'),
		array('ae', 'Ae', 'oe', 'Oe', 'ue', 'Ue', 'ss'),
		$string);
		$string = preg_replace('/[^a-zA-Z0-9_\-]/', $delimiter, $string);
		$string = preg_replace('/'.$delimiter.'{2,}/', $delimiter, $string);
		$string = str_replace('_-_', '-', $string);
		$string = trim($string, $delimiter);

		return $string;
	}
	public static function stripDomain($url)
	{
		return preg_replace('/([a-zA-Z]+:\/\/)?(www\.)?([a-zA-Z0-9äöüÄÖÜ.\-_]+).*/i', '$3', $url);
	}
	public static function stripQuery($url)
	{
		return preg_replace('/([a-zA-Z]+:\/\/)?(www\.)?([a-zA-Z0-9äöüÄÖÜ.\-_]+)(.*)/i', '$4', $url);
	}
}
