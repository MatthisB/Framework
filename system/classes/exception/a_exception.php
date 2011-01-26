<?php

/**
 *
 *  Author:	Matthis
 *  Date:		02.06.2010
 *
 */

namespace Exception;

abstract class a_Exception extends \Exception
{
	protected
		$doLog			= false,
		$logClass		= '',
		$logMessage		= '',
		
		$genuineMessage	= '';
		
	public function __construct($message, $code = 0, \Exception $previous = NULL)
	{
		$this->genuineMessage = $message;
		
		if(is_array($message))
		{
			$message	= implode("</li>\n	<li>", $message);
			$message	= "\n"
						. "<ul>\n"
						. "	<li>".$message."</li>\n"
						. "</ul>\n";
		}
		
		parent::__construct($message, $code, $previous);
	}
	public function getGenuineMessage()
	{
		return $this->genuineMessage;
	}
	public function __destruct()
	{
		$this->log();
	}
	
	protected function log()
	{
		if($this->doLog !== true)
		{
			return;
		}

		$message	 = (empty($this->logMessage) ? $this->genuineMessage : $this->logMessage);
		$message	 = \Filter::LogMessage($message);
		$logClass	 = (empty($this->logClass) ? \Log\Factory::SYTEMERROR : $this->logClass);
		
		$log		 = \Log\Factory::createClass($logClass);
		$log		-> insert($message);
	}
}
