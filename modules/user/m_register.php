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
	/**
	 * insert a new user into database - after that call function m_user::setPassword()
	 * 
	 * @param	string	$nickName
	 * @param	string	$eMail
	 * @return	int		userID
	 */
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
	/**
	 * returns object with all for activation necessary data
	 * 
	 * @param	int		$userID
	 * @return	object	activation-data
	 */
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
	/**
	 * activate the user with id = $userID
	 * 
	 * @param	int		$userID
	 * @return	bool
	 */
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
