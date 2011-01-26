<?php

/**
 *
 *  Author:	Matthis
 *  Date:		13.04.2010
 *
 */

namespace Session;

class Scope extends \a_Singleton
{
	private
		$firstVisit	= false;
	
	protected function __construct()
	{
		if(!array_key_exists('_scope', $_SESSION))
		{
			$_SESSION['_scope'] = array();
			$this->firstVisit = true;
		}
	}
	/**
	 * Define a new Key
	 *
	 * Usage examples:
	 * 	$initSession  = SessionScope::Instance();
	 * 	$initSession -> test1	= 'test Value #1';
	 * 	$initSession -> test1	= 'test Value #1.1';
	 * 	$initSession -> test2	= 'test Value #2';
	 * 	$initSession -> test3	= false;
	 * 	$initSession -> test4	= array('test 3.1', 'test 3.2', 'test 3.3');
	 *
	 * @param	mixed	$key
	 * @param	mixed	$value
	 * @return	bool
	 */
	public function __set($key, $value)
	{
		$_SESSION['_scope'][$key] = $value;
		return true;
	}
	public function __get($key)
	{
		if(array_key_exists($key, $_SESSION['_scope']))
		{
			if(is_array($_SESSION['_scope'][$key]))
			{
				return (array) $_SESSION['_scope'][$key];
			}
				
			return $_SESSION['_scope'][$key];
		}
		return NULL;
	}
	public function __unset($key)
	{
		if(array_key_exists($key, $_SESSION['_scope']))
		{
			unset($_SESSION['_scope'][$key]);
			return true;
		}
		return false;
	}
	public function __isset($key)
	{
		return array_key_exists($key, $_SESSION['_scope']);
	}
	public function isFirstVisit()
	{
		return $this->firstVisit;
	}
	public function getAll()
	{
		return $_SESSION['_scope'];
	}
	public function destroyScope()
	{
		$_SESSION['_scope'] = array();
	}
}
