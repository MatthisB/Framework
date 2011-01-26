<?php

/**
  *
  *  Author:	Matthis
  *  Date:		08.11.2010
  *
  */

namespace Module\User;

class m_Register extends \MVC\a_Model implements \MVC\i_Model
{
	public function insertUser($nickName, $eMail)
	{
		$nickName = \Filter::mySQL_RealEscapeString($nickName);
		$eMail    = \Filter::mySQL_RealEscapeString($eMail);
		
		$query    = new \mySQL\Insert();
		$query   -> table(PREFIX.'user');
		$query   -> setCols(array('nickName', 'email'));
		$query   -> setStack(array($nickName, $eMail));
		$query   -> exeQuery();
		
		return $query -> getInsertID();
	}
	public function getActivationObj($userID)
	{
		$userID = \Filter::Int($userID);
		
		$query  = new \mySQL\Select();
		$query -> selectFrom('ID, nickname as nick, email, MD5(CONCAT(ID, pwHash)) as hash, activationDate', PREFIX.'user');
		$query -> where('ID = '.$userID);
		$query -> exeQuery();
			
		if($query -> NumRows() != 1)
		{
			throw new \Exception\NormalError('There is no user with the ID = "'.$userID.'" !');
		}
			
		return $query -> FetchObj();
	}
	public function activateUser($userID)
	{
		$userID = \Filter::Int($userID);
		$sql    = 'UPDATE
					'.PREFIX.'user
				   SET
				   	activationDate = NOW()
				   WHERE
				   	ID = '.$userID.';';
		$query  = new \mySQL\Query();
		$query -> sqlQuery($sql);
		
		return ($query -> AffectedRows() == 1 ? true : false);
	}
}
