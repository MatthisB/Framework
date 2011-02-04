<?php

/**
  *
  *  Author:	Matthis
  *  Date:		21.06.2010
  *
  */

namespace Template;

class HTML_Cached extends \Template\HTML
{
	private
		$_cache		= NULL,
		$_isCached	= false;
			
	public function __construct($template, $cacheName = NULL, $lifetime = 3600, $dir = './')
	{
		parent::__construct($template, $dir);
		$cacheName			= $dir.($cacheName === NULL ? $template : $cacheName);
		$cacheName			= serialize($cacheName);
		$cacheName			= md5($cacheName);
		$this->_cache		= new \Cache($cacheName, $lifetime);
	}
	public function existCache()
	{
		return $this->_cache->existCache();
	}
	public function deleteCache()
	{
		$this->_cache->deleteCache();	
	}
	public function createCache()
	{
		$this->runTemplate();
		$this->_isCached = true;
	}
	
	protected function runCache()
	{
		$this->_cache->createCache($this->_content);
	}
	protected function runTemplate()
	{
		parent::runTemplate();
		if($this->_isCached !== true)
		{
			$this->runCache();
		}
	}
	protected function loadFile()
	{
		if($this->existCache())
		{
			$this->_content = $this->_cache->returnCache();
		}
		else
		{
			parent::loadFile();
		}
	}
}
