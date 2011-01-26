<?php

/**
 *
 *  Author:	Matthis
 *  Date:		07.06.2010
 *
 */

namespace Helper;

class User
{
	private static
		$fingerPrint = '';

	public static function getIP()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
	public static function generateFingerprint()
	{
		if(!empty(self::$fingerPrint))
		{
			return self::$fingerPrint;
		}

		$fingerprintArray	= array();
		$fingerprintArray[]	= \Registry::Instance()->frameworkConfig->siteHash;
		$fingerprintArray[]	= \Header\Request::getUserAgent();
		$fingerprintArray[]	= \Header\Request::getAcceptCharset();
		$fingerprintArray[] = \Header\Request::getAcceptEncoding();
		$fingerprintArray[]	= \Header\Request::getAcceptLanguage();
		$fingerprintArray[]	= \Header\Request::getAcceptFileTypes();

		$fingerprintArray[]	= substr(self::getIP(), 0, 7);

		$fingerprint		= serialize($fingerprintArray);
		$fingerprint		= md5($fingerprint);

		self::$fingerPrint	= $fingerprint;
		return self::$fingerPrint;
	}

	public static function doesUserExistsByID($ID)
	{
		$ID = \Filter::Int($ID);
		return self::doesUserExistsByX('ID = '.$ID);
	}
	public static function getUserData($ID)
	{
		$ID	= \Filter::Int($ID);
		return self::loadUserData('ID = '.$ID, true);
	}
	public static function getUserIDbyNick($nick)
	{
		$nick = \Filter::mySQL_RealEscapeString($nick);
		$data = self::loadUserData('BINARY nickName = "'.$nick.'"');
		
		if($data == false)
		{
			return false;
		}
		
		return $data -> ID;
	}

	private static function loadUserData($where, $mustExist = false)
	{
		$sql  = new \mySQL\Select();
		$sql -> selectFrom('ID, nickName as Nick, pwHash', PREFIX.'user');
		$sql -> where($where);
		$sql -> exeQuery();
				
		if($sql -> NumRows() !== 1)
		{
			if($mustExist == true)
			{
				trigger_error('Could not load UserData <i>[ WHERE: '.$where.' ]</i>!', E_USER_WARNING);
			}
			return false;
		}

		return $sql -> FetchObj();
	}
	private static function doesUserExistsByX($where)
	{
		$sql  = new \mySQL\Select();
		$sql -> selectFrom('ID', PREFIX.'user');
		$sql -> where($where);
		$sql -> exeQuery();
		
		if($sql -> NumRows() === 1)
		{
			return true;
		}
		
		return false;
	}
}
