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
	
	/**
	 * initalises the Session-Scope
	 * - if _scope doesnt exists in $_SESSION create it
	 */
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
	/**
	 * returns the called $key from Session-Scope
	 * - if $key doesnt exists, retun NULL
	 * 
	 * @param	string	$key
	 */
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
	/**
	 * deletes $key from Session(-Scope)
	 * 
	 * @param	string	$key
	 */
	public function __unset($key)
	{
		if(array_key_exists($key, $_SESSION['_scope']))
		{
			unset($_SESSION['_scope'][$key]);
			return true;
		}
		return false;
	}
	/**
	 * magic function, so you can simply use isset()
	 * 
	 * @param	string	$key
	 */
	public function __isset($key)
	{
		return array_key_exists($key, $_SESSION['_scope']);
	}
	/**
	 * if is first visit on page returns true
	 * 
	 * @return	bool
	 */
	public function isFirstVisit()
	{
		return $this->firstVisit;
	}
	/**
	 * returns the whole SessionScope array
	 */
	public function getAll()
	{
		return $_SESSION['_scope'];
	}
	/**
	 * be careful with this function!
	 * it deletes all Session-Scope variables from session
	 */
	public function destroyScope()
	{
		$_SESSION['_scope'] = array();
	}
}
