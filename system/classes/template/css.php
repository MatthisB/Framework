<?php

/**
  *
  *  Author:	Matthis
  *  Date:		04.02.2011
  *
  */

namespace Template;

class CSS
{
	private
		$vars			= array(),
		$fileName		= '',
		$fileCache		= NULL,
		$fileContent	= '';
		
	
	public function __construct($cssFile, $cacheLifetime = 3600)
	{
		if(!preg_match('/'.preg_quote(ROOT, '/').'.*?/i', $cssFile))
		{
			$cssFile	 = ROOT.$cssFile;
		}

		$cssFile	 = preg_replace('/^(.*?)(\.css)?$/', '$1.css', $cssFile);
		$cssFile	 = \Filter::Folder($cssFile, true);
		
		$this -> fileName	 = $cssFile;
		
		$this -> fileCache	 = new \Cache(md5($this -> fileName), $cacheLifetime);
	}
	public function runTemplate()
	{
		if($this -> fileCache ->existCache())
		{
			$this -> fileContent	  = $this -> fileCache -> returnCache();
			return true;
		}

		if(!is_readable($this -> fileName))
		{
			$this -> fileContent	 = 'File <i>( '.$this -> fileName.' )</i> not found ...';
			return false;
		}
		
		$lines	 = file($this -> fileName);
		foreach($lines as $line)
		{
			$this -> fileContent	.= $this -> parseLine($line);
		}
		
		$this -> compressTemplate();
		
		$this -> fileCache -> createCache($this -> fileContent);
	}
	public function returnTemplate()
	{
		return $this -> fileContent;
	}
	
	private function compressTemplate()
	{
		# remove comments
		$this -> fileContent	 = preg_replace('/\/\*[^*]*\*+([^\/][^*]*\*+)*\//', '', $this -> fileContent);
		
		# remove multiple whitespaces
		$this -> fileContent	 = preg_replace('/ +/', ' ', $this -> fileContent);
		
		# remove line breaks and tabs
		$this -> fileContent	 = str_replace(array("\r\n", "\r", "\n", "\t"), '', $this -> fileContent);
	}
	private function parseLine($line)
	{
		# replace system constants
		$line	 = str_replace('$_SITEPATH', \Helper\URL::$_SITEPATH, $line);
		
		# start define & replace variables
		if(preg_match_all('/\s*\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)(?:\s*:\s*([^;]+))?\s*/', $line, $matches, PREG_SET_ORDER))
		{
			foreach($matches as $match)
			{
				$match[1]	 = trim($match[1]);

				# if is definition of var ...
				if(isset($match[2]))
				{
					$this -> vars[$match[1]]	 = ' '.trim($match[2]);
					$line	 = str_replace($match[0].';', '', $line);
					continue;
				}
				
				# ... else replace with definition - if exists
				if(array_key_exists($match[1], $this -> vars))
				{
					$line	 = str_replace($match[0], $this -> vars[$match[1]], $line);
				}
			}
		}
		
		return $line;
	}
}
