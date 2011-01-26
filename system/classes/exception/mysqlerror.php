<?php

/**
 *
 *  Author:	Matthis
 *  Date:		15.04.2010
 *
 */

namespace Exception;

class mySQLError extends \Exception\a_Exception
{
	protected
		$doLog			= true,
		$logClass		= 'mysqlError';
	
	private function printError($headLine, $errorCode, $errorMessage, $query)
	{
		$template  = new \Template('mysql_error', 'system/files/templates/');
		$template -> headLine		= $headLine;
		$template -> errorCode		= $errorCode;
		$template -> errorMessage	= $errorMessage;
		$template -> query			= $query;
		$template -> currentSite	= \Helper\URL::$_CURRENT;
		$template -> referer		= \Header\Request::getReferer();
		$template -> printTemplate();
		
		$this -> logMessage	= $headLine.'|'.$errorCode.'|'.$errorMessage;
		
		die();
	}
	public function ConnectError(\mysqli $mysql)
	{
		$this->printError('mySQL Connection Failed',
							$mysql->connect_errno,
							$mysql->connect_error,
							'');
	}
	public function QueryError(\mySQL\Query $mysql)
	{
		$this->printError('Error in Query',
							$mysql->errno,
							$mysql->error,
							$mysql->_sqlQuery);
	}
	public function LogicError()
	{
		$this->printError('Logic Error',
							'0',
							$this->getMessage(),
							'');
	}
}
