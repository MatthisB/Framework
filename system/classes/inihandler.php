<?php

/**
 *
 *  Author:	Matthis
 *  Date:		15.04.2010
 *
 */


class iniHandler
{
	const
		READ	= 0,
		CREATE	= 1;

	private
		$mode		= 1,
		$iniDir		= '',
		$iniFile	= '',
		$iniArray	= array();

	/*
	 * mode; 0 = read only, 1 = override / create
	 */
	public function __construct($iniFile, $mode = 1, $iniDir = 'system/files/inis/')
	{
		$this->mode     = $mode;
		$this->iniDir	= ROOT.\Filter::Folder($iniDir, true);
		$this->iniFile	= \Filter::File($iniFile);

		try
		{
			$this->loadFile();
		}
		catch(\Exception\FatalError $exception)
		{
			echo $exception->getMessage();
		}
	}
	public function __isset($key)
	{
		return array_key_exists($key, $this->iniArray);
	}
	public function __get($key)
	{
		if(array_key_exists($key, $this->iniArray))
		{
			return $this->iniArray[$key];
		}
		return NULL;
	}
	public function __set($key, $value)
	{
		if(!array_key_exists($key, $this->iniArray) || !$this->mode !== 0)
		{
			$this->iniArray[$key] = $value;
			return true;
		}
		return false;
	}
	public function getArray()
	{
		return $this->iniArray;
	}
	public function updateFile()
	{
		try
		{
			$this->saveFile();
			return true;
		}
		catch(\Exception\FatalError $exception)
		{
			echo $exception->getMessage();
		}
	}

	private function loadFile()
	{
		$this->iniFile	= strtolower($this->iniFile);
		$this->iniFile  = preg_replace('/([a-zA-Z0-9]+)(\.ini)?/', $this->iniDir.'$1.ini', $this->iniFile);
		if(!is_readable($this->iniFile))
		{
			if($this->mode == 0 || !file_put_contents($this->iniFile, '<?php ?>'))
			{
				throw new \Exception\FatalError('Could neither load nor create .ini <i>[ '.$this->iniFile.' ]</i> File!');
			}
		}
		$this->iniArray = parse_ini_file($this->iniFile, true);
	}
	private function saveFile()
	{
		try
		{
			if($this->mode == 0)
			{
				throw new \Exception\FatalError('It isn\'t allowed to save this .ini ( <i>'.$this->iniFile.'</i> ) - mode is 0 ( <i>read only</i> ).');
			}

			$update  = "<?php\n\n";
			foreach($this->iniArray as $key => $value)
			{
				if(!is_array($value))
				{
					$update .= $key." = '".$value."'\n";
				}
				else
				{
					$update .= "\n\n[".$key."]\n";
					foreach($value as $subkey => $subvalue)
					{
						$update .= $subkey." = '".$subvalue."'\n";
					}
				}
			}
			$update .= "\n?>";
				
			if(!file_put_contents($this->iniFile, $update))
			{
				throw new \Exception\FatalError('Could not update .ini <i>[ '.$this->iniFile.' ]</i> File!');
			}

			$this->loadFile();
			return true;
		}
		catch(\Exception\FatalError $exception)
		{
			echo $exception->getMessage();
		}
	}
}
