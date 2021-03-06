<?php

/**
 *
 *  Author:	Matthis
 *  Date:		10.04.2010
 *
 */

class Registry extends \a_Singleton
{
	private static
		$vars = array();

	protected function __construct()
	{
		$this->loadStandardConfigs();
	}
	/**
	 * add a variable to registry
	 * 
	 * @param	string	$key
	 * @param	mixed	$value
	 * @return	bool
	 */
	public function __set($key, $value)
	{
		if(!array_key_exists($key, self::$vars))
		{
			self::$vars[$key] = $value;
			return true;
		}
		return false;
	}
	/**
	 * returns $key-value from registry
	 * 
	 * @param	string	$key
	 */
	public function __get($key)
	{
		if(array_key_exists($key, self::$vars))
		{
			return self::$vars[$key];
		}

		trigger_error('Called $key <i>[ '.$key.' ]</i> doesn\'t exist!', E_USER_WARNING);
		return NULL;
	}
	
	/**
	 * just pre-load some necessaray or often used *.ini files
	 */
	private function loadStandardConfigs()
	{
		self::$vars['frameworkConfig']		= new \iniHandler('framework', \iniHandler::READ);
		self::$vars['mySQL_Standard_Data']	= new \iniHandler('mysql', \iniHandler::READ);
		self::$vars['templateConfig']		= new \iniHandler('templates', \iniHandler::READ);
	}
}
