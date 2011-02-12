<?php

/**
 *
 *  Author:	Matthis
 *  Date:		31.05.2010
 *
 */

class Benchmark extends \a_Singleton
{
	private
		$_startTimes	= array();

	private static
		$loadedClasses	= 0,
		$loadedQueries	= 0;
	 
	/**
	 * start a new benchmark timer
	 * 
	 * @param 	mixed	$name
	 */
	public function startTimer($name)
	{
		$this->_startTimes[$name] = $this->MicroTime();
	}
	/**
	 * return the difference between start and now
	 * 
	 * @param	mixed	$name
	 * @param	string	$format
	 * @return	string
	 */
	public function getResult($name, $format = '%f')
	{
		$result = (float)((float)$this->MicroTime() - (float)$this->_startTimes[$name]);
		return sprintf($format, $result);
	}

	/**
	 * raises the loaded-classes-counter ( +1 )
	 */
	public static function raiseLoadedClasses()
	{
		self::$loadedClasses++;
	}
	/**
	 * returns the loaded-classes-counter-value
	 */
	public static function getLoadedClasses()
	{
		return self::$loadedClasses;
	}
	/**
	 * raises the mysql-query-counter ( +1 )
	 */
	public static function raiseLoadedQueries()
	{
		self::$loadedQueries++;
	}
	/**
	 * returns the value of the query counter
	 */
	public static function getLoadedQueries()
	{
		return self::$loadedQueries;
	}

	
	/**
	 * retruns float of microtime()
	 */
	private function MicroTime()
	{
		$time = explode(' ', microtime());
		return (float)((float)$time[0] + (float)$time[1]);
	}
}
