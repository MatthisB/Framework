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
	 
	public function startTimer($name)
	{
		$this->_startTimes[$name] = $this->MicroTime();
	}
	public function getResult($name, $format = '%f')
	{
		$result = (float)((float)$this->MicroTime() - (float)$this->_startTimes[$name]);
		return sprintf($format, $result);
	}

	public static function raiseLoadedClasses()
	{
		self::$loadedClasses++;
	}
	public static function getLoadedClasses()
	{
		return self::$loadedClasses;
	}
	public static function raiseLoadedQueries()
	{
		self::$loadedQueries++;
	}
	public static function getLoadedQueries()
	{
		return self::$loadedQueries;
	}

	private function MicroTime()
	{
		$time = explode(' ', microtime());
		return (float)((float)$time[0] + (float)$time[1]);
	}
}
