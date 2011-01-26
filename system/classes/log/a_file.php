<?php

/**
  *
  *  Author:	Matthis
  *  Date:		10.12.2010
  *
  */

namespace Log;

abstract class a_File extends \Log\Factory
{
	protected
		$fileName = '';

	public function readOut()
	{
		if(($fileContent = file_get_contents($this->fileName)) === false)
		{
			trigger_error('Coult not readout <i>'.$this->fileName.'</i>!', E_USER_ERROR);
		}

		return $fileContent;
	}
	public function dumpEnries()
	{
		if(!file_put_contents($this->fileName, ''))
		{
			trigger_error('Could not reset <i>'.$this->fileName.'</i>!', E_USER_ERROR);
		}
	}

	public function saveEntries()
	{
		if(!file_put_contents($this->fileName, $this->_entries, FILE_APPEND))
		{
			trigger_error('Could not write into <i>'.$this->fileName.'</i>!', E_USER_ERROR);
		}

		$this->_entries = array();
	}
	public function formatEntry($message)
	{
		return sprintf("[%s] %s\n", date('d.m.Y H:i'), $message);
	}
}
