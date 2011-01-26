<?php

/**
 *
 *  Author:	Matthis
 *  Date:		02.05.2010
 *
 */

namespace Session;

/*
 CREATE TABLE `fw_sessions` (
 `sessionID` VARCHAR(32) NOT NULL,
 `sessionData` TEXT NOT NULL,
 `sessionDate` DATETIME NOT NULL,
 PRIMARY KEY (`sessionID`)
 ) ENGINE = MYISAM ;
 */

class SaveHandler extends \a_Singleton
{
	private
		$name = '';

	private static
		$initialized = false;

	protected function __construct()
	{
		if(ini_set('session.use_cookies'     , 1) === false
		|| ini_set('session.use_only_cookies', 1) === false
		|| ini_set('session.use_trans_sid'   , 0) === false)
		{
			trigger_error('Could not set .ini-session Settings!', E_USER_WARNING);
		}
			
		session_set_save_handler(array(&$this, 'open'),
		array(&$this, 'close'),
		array(&$this, 'read'),
		array(&$this, 'write'),
		array(&$this, 'destroy'),
		array(&$this, 'gc'));
		register_shutdown_function('session_write_close');

		$this->deleteOldEntries();

		session_name('fw_'.\Registry::Instance()->frameworkConfig->siteHash);
		session_start();

		if(!array_key_exists('_init', $_SESSION)
		|| $_SESSION['_init'] != \Registry::Instance()->frameworkConfig->siteHash
		|| !array_key_exists('_fingerprint', $_SESSION)
		|| $_SESSION['_fingerprint'] != \Helper\User::generateFingerprint())
		{
			session_regenerate_id(true);
			$_SESSION['_init']			= \Registry::Instance()->frameworkConfig->siteHash;
			$_SESSION['_fingerprint']	= \Helper\User::generateFingerprint();
		}
	}
	public function close()
	{
		return true;
	}
	public function open($savePath, $name)
	{
		$this->name = $name;
	}
	public function read($sessionID)
	{
		$sessionID	=  \Filter::mySQL_RealEscapeString($sessionID);
		$query		=  new \mySQL\Select();
		$query		-> selectFrom('sessionData', PREFIX.'sessions');
		$query		-> where('sessionID = "'.$sessionID.'"');
		$query		-> exeQuery();

		if($query->NumRows() > 0)
		{
			$res  = $query->FetchObj();
			$data = $res->sessionData;
			$data = explode('|', $data);
			$name = array_shift($data);
			$data = implode('|', $data);
			$data = stripslashes($data);
			$data = unserialize($data);
				
			return $data;
		}

		return '';
	}
	public function write($sessionID, $sessionData)
	{
		$sessionData	=  $this->name.'|'.addslashes(serialize($sessionData));
		$sessionID		=  \Filter::mySQL_RealEscapeString($sessionID);;
		$sql			=  'REPLACE INTO `'.PREFIX.'sessions` VALUES ( "'.$sessionID.'", "'.$sessionData.'", NOW() );';
		$query			=  new \mySQL\Query();
		$query			-> Query($sql);

		return ($query->AffectedRows() > 0 ? true : false);
	}
	public function destroy($sessionID)
	{
		$sessionID	=  \Filter::mySQL_RealEscapeString($sessionID);
		$query		=  new \mySQL\Query();
		$query		-> Delete(PREFIX.'sessions', 'sessionID = "'.$sessionID.'"');

		return ($query->AffectedRows() > 0 ? true : false);
	}
	public function WriteAndClose()
	{
		session_write_close();
	}
	public function gc()
	{
		$this->deleteOldEntries();
	}

	private function deleteOldEntries()
	{
		$maxLifeTime = \Registry::Instance()->frameworkConfig->session['maxLifeTime'];
		$maxLifeTime = \Filter::mySQL_RealEscapeString($maxLifeTime);

		$query  = new \mySQL\Query();
		$query -> Delete(PREFIX.'sessions', '(`sessionDate` + INTERVAL '.$maxLifeTime.') < NOW()');
	}
}
