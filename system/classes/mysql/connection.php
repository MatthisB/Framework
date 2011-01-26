<?php

/**
 *
 *  Author:	Matthis
 *  Date:		15.04.2010
 *
 */

namespace mySQL;

class Connection
{
	private
		$_data			= array(),
		$_connection	= NULL;

	public function __construct(array $mySQL = NULL)
	{
		if($mySQL == NULL)
		{
			$this->_data = \Registry::Instance()->mySQL_Standard_Data->getArray();
		}
		else
		{
			$this->_data = $mySQL;
		}

		$this->Connect();
	}
	public function getConnection()
	{
		return $this->_connection;
	}

	private function Connect()
	{
		try
		{
			$mustHave = array('host', 'user', 'password', 'database');
			foreach($mustHave as $key => $value)
			{
				if(!array_key_exists($value, $this->_data))
				{
					varDump($value, $this->_data);
					throw new \Exception\mySQLError('Data Array isn\'t complete - must include: <i>host, user, password, database</i>');
				}
			}
		}
		catch(\Exception\mySQLError $error)
		{
			echo $error->LogicError();
		}
			
			
		try
		{
			$this->_connection = new \mysqli($this->_data['host'], $this->_data['user'], $this->_data['password'], $this->_data['database']);

			if($this->_connection->connect_error)
			{
				throw new \Exception\mySQLError('mySQL Connection Error.');
			}
			return true;
		}
		catch(\Exception\mySQLError $error)
		{
			echo $error->ConnectError($this->_connection);
		}
		return false;
	}
}
