<?php

/**
 *
 *  Author:	Matthis
 *  Date:		01.07.2010
 *
 */

namespace Cookie;

/*
 CREATE TABLE `fw_cookies` (
 `ID` VARCHAR(32) NOT NULL UNIQUE,
 `name` VARCHAR(255) NOT NULL,
 `Data` TEXT NOT NULL,
 `createDate` DATETIME NOT NULL,
 `expireDate` DATETIME NOT NULL,
 `validTime` int(11) NOT NULL,

 PRIMARY KEY (`ID`)
 ) ENGINE = MYISAM ;
 */

class Helper
{
	private
		$renewCookie	= false,
		$newCookie		= false,
		$delCookie		= false,
		$cookieID		= '',
		$cookieName		= '';

	private static
		$_cookies		= array();

	/**
	 * initialise; load data, cache data, ...
	 * 
	 * @param	string	$name
	 */
	public function __construct($name)
	{
		$this->cookieName = \Filter::systemID($name);

		if(!array_key_exists($this->cookieName, self::$_cookies))
		{
			$data = $this->loadData();
			if($data === false)
			{
				$this->newCookie = true;
			}
				
			self::$_cookies[$this->cookieName] = ($data !== false ? $data : array());
		}
	}
	/**
	 * checks  if the cookie exists
	 */
	public function exist()
	{
		return !$this->newCookie;
	}
	/**
	 * just returns the cookie ID
	 */
	public function getCookieID()
	{
		return $this->cookieID;
	}
	/**
	 * returns ( if exists ) the called data ( $key ) from cookie
	 * 
	 * @param	mixed	$key
	 */
	public function __get($key)
	{
		return (array_key_exists($key, self::$_cookies[$this->cookieName]['Data']) ? self::$_cookies[$this->cookieName]['Data'][$key] : false);
	}
	/**
	 * inserts / overwrites the $value with $key in cookie data
	 * 
	 * @param	mixed	$key
	 * @param	mixed	$value
	 */
	public function __set($key, $value)
	{
		self::$_cookies[$this->cookieName]['Data'][$key] = $value;
	}
	/**
	 * creates the 'real' cookie to link it with the data in mysql
	 * 
	 * @param	int		$expire
	 * @param	string	$path
	 * @param	string	$domain
	 * @param	bool	$secure
	 * @param	bool	$httponly
	 */
	public function create($expire, $path = '/', $domain = '', $secure = false, $httponly = true)
	{
		if($this->exist())
		{
			unset(self::$_cookies[$this->cookieName]);
		}

		$this->cookieID	= md5($this->cookieName.microtime().\Helper\User::generateFingerprint());
		$expire			= \Filter::Int($expire);

		self::$_cookies[$this->cookieName]['ID']		= $this->cookieID;
		self::$_cookies[$this->cookieName]['Data']		= array();
		self::$_cookies[$this->cookieName]['validTime'] = $expire;

		\Cookie\Real::setCookie($this->cookieName, $this->cookieID, ($expire + time()), $path, $domain, $secure, $httponly);
	}
	/**
	 * renews the 'real' cookie ( if sessionCheck == true on first visit )
	 * 
	 * @param	bool	$sessionCheck
	 * @param	string	$path
	 * @param	string	$domain
	 * @param	bool	$secure
	 * @param	bool	$httponly
	 */
	public function renew($sessionCheck = true, $path = '/', $domain = '', $secure = false, $httponly = true)
	{
		if(($sessionCheck && \Session\Scope::Instance()->isFirstVisit() !== true)
		|| $this->newCookie === true)
		{
			return;
		}

		$this->renewCookie = true;

		$expire		= time() + self::$_cookies[$this->cookieName]['validTime'];

		\Cookie\Real::setCookie($this->cookieName, $this->cookieID, $expire, $path, $domain, $secure, $httponly);
	}
	/**
	 * deletes the cookie
	 */
	public function delete()
	{
		$this-> delCookie = true;
		$sql  = new \mySQL\Query();
		$sql -> Delete(PREFIX.'cookies', 'ID = "'.$this->cookieID.'"');

		\Cookie\Real::delCookie($this->cookieName);

		unset(self::$_cookies[$this->cookieName]);
	}
	/**
	 * insert cookie, save changes, ...
	 */
	public function __destruct()
	{
		if($this->exist() !== true || $this->delCookie === true)
		{
			return;
		}

		$data  = serialize(self::$_cookies[$this->cookieName]['Data']);
		$data  = \Filter::mySQL_RealEscapeString($data);

		$renew = ($this->renewCookie ? ', expireDate = (NOW() + INTERVAL '.self::$_cookies[$this->cookieName]['validTime'].' SECOND)' : '');

		$sql   = new \mySQL\Query();
		$query = 'INSERT INTO
					'.PREFIX.'cookies
				  	( ID, name, Data, createDate, expireDate, validTime )
				  VALUES
				  	( "'.$this->cookieID.'",
				  	  "'.$this->cookieName.'",
				  	  "'.$data.'",
				  	  NOW(),
				  	  (NOW() + INTERVAL '.self::$_cookies[$this->cookieName]['validTime'].' SECOND ),
				  	  '.self::$_cookies[$this->cookieName]['validTime'].'
				  	)
				  ON DUPLICATE KEY UPDATE
				  	Data       = "'.$data.'"'.$renew.';';
		$sql  -> sqlQuery($query);
	}

	/**
	 * load and unserialize the cookie data
	 */
	private function loadData()
	{
		if(\Cookie\Real::exists($this->cookieName))
		{
			$this->cookieID = \Cookie\Real::getCookie($this->cookieName);
			$this->cookieID = \Filter::systemID($this->cookieID);
		}
		else
		{
			return false;
		}

		$sql  = new \mySQL\Select();
		$sql -> selectFrom('ID, name, Data, createDate, expireDate, validTime', PREFIX.'cookies');
		$sql -> where('ID = "'.$this->cookieID.'"');
		$sql -> exeQuery();

		if($sql->NumRows != 1)
		{
			return false;
		}

		$result = $sql->FetchArr();
		$result['Data'] = stripslashes($result['Data']);
		$result['Data'] = unserialize($result['Data']);

		return $result;
	}
}
