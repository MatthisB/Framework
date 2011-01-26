<?php

/**
 *
 *  Author:	Matthis
 *  Date:		26.07.2010
 *
 */

namespace Helper;

class Login extends \a_Singleton
{
	private static
	$isLoggedIn	= false;

	protected function __construct()
	{
		if(!isset(\Session\Scope::Instance() -> user))
		{
			\Session\Scope::Instance() -> user = new \ArrayObject();
		}
		if(self::$isLoggedIn === true)
		{
			return true;
		}

		$cookie  = new \Cookie\Helper('fw_login');
		if($cookie -> exist())
		{
			if($this->checkLogin($cookie -> userID, $cookie -> pwHash) === true)
			{
				$cookie -> renew(true);

				\Session\Scope::Instance() -> user = \Helper\User::getUserData($cookie -> userID);

				self::$isLoggedIn = true;
				return true;
			}
			else
			{
				$cookie -> delete();
			}
		}

		self::$isLoggedIn = false;
		return false;
	}

	public static function isLoggedIn()
	{
		return self::$isLoggedIn;
	}
	public static function checkLogin($userID, $pwHash)
	{
		$userID	= \Filter::mySQL_RealEscapeString($userID);
		$pwHash	= \Filter::mySQL_RealEscapeString($pwHash);

		$sql  = new \mySQL\Select();
		$sql -> selectFrom('activationDate', PREFIX.'user');
		$sql -> where('ID = "'.$userID.'" AND BINARY pwHash = "'.$pwHash.'"');
		$sql -> exeQuery();

		if($sql -> NumRows() == 1)
		{
			if($sql -> FetchObj() -> activationDate == '0000-00-00 00:00:00')
			{
				return -1;
			}
			
			self::$isLoggedIn = true;
			return true;
		}

		return false;
	}
	public static function createPwHash($pw)
	{
		$pwHash  = \Registry::Instance() -> frameworkConfig -> siteHash;
		$pwHash .= $pw;
		$pwHash  = md5($pwHash);

		return $pwHash;
	}
}
