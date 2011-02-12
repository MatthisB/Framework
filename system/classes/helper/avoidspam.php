<?php

/**
 *
 *  Author:	Matthis
 *  Date:		28.07.2010
 *
 */

/*
CREATE TABLE `fw_avoidspam`
(
	`type`			varchar(32)	NOT NULL,
	`typeID`		varchar(32)	NOT NULL default 0,
	`userIP`		varchar(50)	NOT NULL,
	`insertTime`	timestamp	NOT NULL default CURRENT_TIMESTAMP,
	`hits`			int(11)		NOT NULL default 1,

	UNIQUE `entry` ( `type` , `typeID`, `userIP` )
);
*/

namespace Helper;

class AvoidSpam
{
	private
		$type		= '',
		$typeID		= '',
		$blockIP	= '',
		$blockSecs	= '';

	/**
	 * define the settings
	 * 
	 * @param	string	$blockTime
	 * @param	string	$type
	 * @param	string	$typeID
	 * @param	string	$ip
	 */
	public function __construct($blockTime, $type, $typeID = '', $ip = NULL)
	{
		$this->blockTime	= \Filter::mySQL_RealEscapeString($blockTime);

		$this->type			= \Filter::mySQL_RealEscapeString($type);
		$this->typeID		= \Filter::mySQL_RealEscapeString($typeID);

		$this->blockIP		= ($ip != NULL ? $ip : \Helper\User::getIP());
		$this->blockIP		= \Filter::mySQL_RealEscapeString($this->blockIP);

		$this->DeleteOldEntries();
	}
	/**
	 * returns the hits -
	 * - if there is no entry that fits, returns false
	 */
	public function CheckHits()
	{
		$sql  = new \mySQL\Select();
		$sql -> selectFrom('hits', PREFIX.'avoidspam');
		$sql -> where('type = "'.$this->type.'" AND typeID = "'.$this->typeID.'" AND userIP = "'.$this->blockIP.'"');
		$sql -> exeQuery();

		if($sql -> NumRows() == 1)
		{
			return $sql -> FetchObj() -> hits;
		}

		return false;
	}
	/**
	 * insert a new entry into mysql table
	 * on duplicate key update hits++
	 */
	public function Insert()
	{
		$query	= 'INSERT INTO
						'.PREFIX.'avoidspam
				    	(type, typeID, userIP)
				    VALUES
				    	("'.$this->type.'", "'.$this->typeID.'", "'.$this->blockIP.'")
				    ON DUPLICATE KEY UPDATE
				    	hits = hits + 1;';
		$sql	= new \mySQL\Query();
		$sql   -> sqlQuery($query);
	}
	/**
	 * delete entry
	 */
	public function Delete()
	{
		$sql  = new \mySQL\Query();
		$sql -> Delete(PREFIX.'avoidspam', 'type = "'.$this->type.'" AND typeID = "'.$this->typeID.'" AND userIP = "'.$this->blockIP.'"');
	}

	/**
	 * delete expired entries
	 */
	private function DeleteOldEntries()
	{
		$sql  = new \mySQL\Query();
		$sql -> Delete(PREFIX.'avoidspam', 'type = "'.$this->type.'" AND (insertTime + INTERVAL '.$this->blockTime.') < NOW()');
	}
}
