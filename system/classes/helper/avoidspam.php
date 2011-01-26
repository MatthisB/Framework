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

	public function __construct($blockTime, $type, $typeID = '', $ip = NULL)
	{
		$this->blockTime	= \Filter::mySQL_RealEscapeString($blockTime);

		$this->type			= \Filter::mySQL_RealEscapeString($type);
		$this->typeID		= \Filter::mySQL_RealEscapeString($typeID);

		$this->blockIP		= ($ip != NULL ? $ip : \Helper\User::getIP());
		$this->blockIP		= \Filter::mySQL_RealEscapeString($this->blockIP);

		$this->DeleteOldEntries();
	}
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
	public function Delete()
	{
		$sql  = new \mySQL\Query();
		$sql -> Delete(PREFIX.'avoidspam', 'type = "'.$this->type.'" AND typeID = "'.$this->typeID.'" AND userIP = "'.$this->blockIP.'"');
	}

	private function DeleteOldEntries()
	{
		$sql  = new \mySQL\Query();
		$sql -> Delete(PREFIX.'avoidspam', 'type = "'.$this->type.'" AND (insertTime + INTERVAL '.$this->blockTime.') < NOW()');
	}
}
