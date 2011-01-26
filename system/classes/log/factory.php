<?php

/**
 *
 *  Author:	Matthis
 *  Date:		23.06.2010
 *
 */

namespace Log;

abstract class Factory implements \i_Factory
{
	const
		ERROR	= 0,
		WARN	= 1,
		NOTICE	= 2,
		DEBUG	= 3;

	const
		SYTEMERROR	= 'SystemError',
		MYSQLERROR	= 'mysqlError';

	protected
		$_logLevel	= 0,
		$_entries	= array();

	private static
		$logLevels	= array();

	final public static function createClass($className)
	{
		if(empty(self::$logLevels))
		{
			self::loadLogLevel();
		}
		$logLevel = (array_key_exists($className, self::$logLevels) ? self::$logLevels[$className] : self::$logLevels['default']);

		$className = '\\Log\\'.$className;
		$fileName  = ROOT.classFileName($className);
		if(!is_readable($fileName))
		{
			trigger_error('Log Class <i>( '.$className.' )</i> does not exist!', E_USER_ERROR);
		}

		$obj  = new $className();
		$obj -> setLogLevel($logLevel);
		return $obj;
	}

	abstract public function readOut();
	abstract public function dumpEnries();

	abstract public function saveEntries();
	abstract public function formatEntry($message);

	public function insert($message, $level = self::ERROR)
	{
		$level = \Filter::Int($level);
		if($this->_logLevel >= $level)
		{
			$this->_entries[] = $this->formatEntry($message);
		}
	}
	public function setLogLevel($level)
	{
		$this->_logLevel = \Filter::Int($level);
	}
	public function __destruct()
	{
		$this->saveEntries();
	}

	private static function loadLogLevel()
	{
		$ini  = new \iniHandler('errorlevel', \iniHandler::READ);
		self::$logLevels = $ini -> getArray();

		if(!array_key_exists('default', self::$logLevels))
		{
			trigger_error('No <i>default</i> Log Level!', E_USER_WARNING);
			self::$logLevels['default'] = 0;
		}
	}
}
