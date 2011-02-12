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

	/**
	 * initialise the cache class
	 * - filename should be unique
	 * - lifetime in seconds
	 * 
	 * @param	string	$filename
	 * @param	int		$lifetime
	 */
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
	/**
	 * checks if cache-file exists
	 */
	public function existCache()
	{
		return is_readable($this->_filename);
	}
	/**
	 * prints the cache content
	 */
	public function printCache()
	{
		$this->loadCache();
		echo $this->_cacheFile;
	}
	/**
	 * returns the cache content
	 */
	public function returnCache()
	{
		$this->loadCache();
		return $this->_cacheFile;
	}
	/**
	 * creates the cache-file and write content into it
	 * 
	 * @param	mixed	$content
	 */
	public function createCache($content)
	{
		$this->_cacheFile = $content;
		if(file_put_contents($this->_filename, $this->_cacheFile))
		{
			return true;
		}
		return false;
	}
	/**
	 * deletes the cache-file
	 */
	public function deleteCache()
	{
		if(unlink($this->_filename))
		{
			return true;
		}
		trigger_error('Could not delete Cache <i>[ '.$this->_filename.' ]</i>', E_USER_ERROR);
		return false;
	}

	/**
	 * returns the content of cache-file
	 */
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
