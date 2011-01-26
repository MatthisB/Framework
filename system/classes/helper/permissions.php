<?php

/**
  *
  *  Author:	Matthis
  *  Date:		13.09.2010
  *
  */

/*
CREATE TABLE `fw_permissionNames`
(
	`ID`			int(11)			NOT NULL AUTO_INCREMENT,
	`name`			varchar(255)	NOT NULL,
	`description`	varchar(255)	NOT NULL,
	
	PRIMARY KEY (`ID`)
);
CREATE TABLE `fw_permissionLinks`
(
	`userID`	int(11)		NOT NULL,
	`permID`	int(11)		NOT NULL,
	
	UNIQUE `entry` ( `userID`, `permID` )
);
*/

namespace Helper;

class Permissions extends \a_Singleton
{
	private
		$permissions		= array(),
		$userPermissions	= array();
		
	protected function __construct()
	{
		$this->loadPerms();
	}
	
	public static function __callstatic($method, $args)
	{
		if(\isValid::Permission($method, true))
		{
			$thisObj = \Helper\Permissions::Instance();
			return $thisObj -> checkPerm($method, $args);
		}
		
		trigger_error('Undefined function <i>[ Permissions::'.$method.' ]</i> called!', E_USER_ERROR);
	}
	
	private function loadPerms()
	{
		$sql  = new \mySQL\Select();
		$sql -> selectFrom('ID, name', PREFIX.'permissionNames');
		$sql -> exeQuery();
		
		while($result = $sql -> FetchObj())
		{
			$this -> permissions[$result->name]	= $result->ID;
		}
	}
	private function checkPerm($permName, $args = array())
	{
		if(\isValid::Permission($permName, true))
		{
			$permName = substr($permName, 2);
		}
		if(!\isValid::Permission($permName))
		{
			trigger_error('Requested permission isn\'t valid!', E_USER_ERROR);
			return false;
		}
		
		if(array_key_exists('userID', $args))
		{
			$userID	= \Filter::Int($args['userID']);
		}
		else
		{
			if(!LOGGEDIN)
			{
				return false;
			}
			
			$userID	= \Session\Scope::Instance() -> user['ID'];
		}
		
		if(!array_key_exists($permName, $this->permissions))
		{
			trigger_error('Requested permission <i>[ '.$permName.' ]</i> doesn\'t exist!', E_USER_WARNING);
			return false;
		}
		
		if(!array_key_exists($userID, $this->userPermissions))
		{
			$this->userPermissions[$userID]	= array();
		}
		
		if(!array_key_exists($permName, $this->userPermissions[$userID]))
		{
			$sql  = new \mySQL\Select();
			$sql -> selectFrom('permID', PREFIX.'permissionLinks');
			$sql -> where('userID = '.$userID.' AND permID = '.$this->permissions[$permName]);
			$sql -> exeQuery();
			
			$this -> userPermissions[$userID][$permName]	= ($sql -> NumRows() == 1 ? true : false);
		}
		
		return $this -> userPermissions[$userID][$permName];
	}
}
