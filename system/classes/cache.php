<?php

/**
 *
 *  Author:	Matthis
 *  Date:		19.06.2010
 *
 */

class Cache
{
	protected
		$_filename	= '',
		$_lifetime	= 0,
		$_cacheFile	= '';

	# lifetime in sec
	public function __construct($filename, $lifetime)
	{
		$this->_lifetime = Filter::Int($lifetime);
		if($this->_lifetime <= 0)
		{
			trigger_error('Cache-Lifetime <i>[ '.$lifetime.' sec ]</i> doesn\'t make sense ...<br />( set to 1 )', E_USER_WARNING);
			$this->_lifetime = 1;
		}

		$this->_filename = Filter::File($filename);
		$this->_filename = ROOT.'system/files/cache/'.$this->_filename.'.cache';

		$filemtime = @filemtime($this->_filename);
		if($filemtime !== false && $filemtime < (time() - $this->_lifetime))
		{
			$this->deleteCache();
		}
	}
	public function existCache()
	{
		return is_readable($this->_filename);
	}
	public function printCache()
	{
		$this->loadCache();
		echo $this->_cacheFile;
	}
	public function returnCache()
	{
		$this->loadCache();
		return $this->_cacheFile;
	}
	public function createCache($content)
	{
		$this->_cacheFile = $content;
		if(file_put_contents($this->_filename, $this->_cacheFile))
		{
			return true;
		}
		return false;
	}
	public function deleteCache()
	{
		if(unlink($this->_filename))
		{
			return true;
		}
		trigger_error('Could not delete Cache <i>[ '.$this->_filename.' ]</i>', E_USER_ERROR);
		return false;
	}

	private function loadCache()
	{
		if(!empty($this->_cacheFile))
		{
			return;
		}

		if($this->existCache() === false
		|| ($fileContent = file_get_contents($this->_filename)) === false)
		{
			$this->_cacheFile = '';
			return false;
		}

		$this->_cacheFile = $fileContent;
	}
}
