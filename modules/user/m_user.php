<?php

/**
  *
  *  Author:	Matthis
  *  Date:		25.10.2010
  *
  */

namespace Module\User;

/*
CREATE TABLE IF NOT EXISTS `fw_user`
(
	`ID`					int(11)			NOT NULL	AUTO_INCREMENT,
	
	`pwHash`				varchar(32)		NOT NULL,
	`nickName`				varchar(255)	NOT NULL	UNIQUE,
	`firstName`				varchar(255)	NOT NULL,
	`lastName`				varchar(255)	NOT NULL,
	`gender`				tinyint(1)		NOT NULL	default 0,
	`birthday`				date			NOT NULL,

	`icq`					varchar(9)		NOT NULL,
	`email`					varchar(255)	NOT NULL,
	`skype`					varchar(255)	NOT NULL,
	`msn`					varchar(255)	NOT NULL,
	`website`				varchar(255)	NOT NULL,

	`newsletter`			tinyint(1)		NOT NULL	default 1,
	`activationDate`		datetime		NOT NULL,
	`registrationDate`		timestamp		NOT NULL	default CURRENT_TIMESTAMP,
	
	`count_comments`		int(11)			NOT NULL	default 0,
	`count_forum_posts`		int(11)			NOT NULL	default 0,
	`count_forum_topics`	int(11)		NOT NULL	default 0,
	
	PRIMARY KEY (`ID`)
) ENGINE = MyISAM;
*/

class m_User extends \MVC\a_Model implements \MVC\i_Model
{
	public function updateUser()
	{
		
	}
	public function setPassword($userID, $password)
	{
		$userID  = \Filter::mySQL_RealEscapeString($userID);
		$pwHash  = \Helper\Login::createPwHash($password);

		$query   = new \mySQL\Update();
		$query  -> table(PREFIX.'user');
		$query  -> where('ID = "'.$userID.'"');
		$query  -> pwHash = $pwHash;
		$query  -> exeQuery();
		
		if($query -> AffectedRows() !== 1)
		{
			trigger_error('Something went wrong during updating the password!', E_USER_ERROR);
		}
	}
	
	public function nickAlreadyInUse($nick)
	{
		$nick	= \Filter::mySQL_RealEscapeString($nick);
		return $this->xAlreadyInUse('nickname = "'.$nick.'"');
	}
	public function emailAlreadyInUse($email)
	{
		$email	= \Filter::mySQL_RealEscapeString($email);
		return $this->xAlreadyInUse('email = "'.$email.'"');
	}
	
	private function xAlreadyInUse($where)
	{
		$query	 = new \mySQL\Select();
		$query	-> selectFrom('ID', PREFIX.'user');
		$query	-> where($where);
		$query	-> exeQuery();
		
		return ($query->NumRows() == 0 ? false : true);
	}
}
