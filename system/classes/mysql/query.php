<?php

/**
 *
 *  Author:	Matthis
 *  Date:		15.04.2010
 *
 */

namespace mySQL;

class Query
{
	public
		$errno			= 0,
		$error			= '',
		$_sqlQuery  	= '',
		$NumRows		= 0,
		$AffectedRows	= 0;

	protected
		$_result		= NULL,
		$_connection	= NULL;

	final public function __construct(\mySQL\Connection $connection = NULL)
	{
		if($connection == NULL)
		{
			$this->_connection = \Registry::Instance()->mySQL_Standard_Connection;
		}
		else
		{
			$this->_connection = $connection;
		}
	}
	public function __call($function, $args)
	{
		if(!is_callable(array($this->_connection, $function)))
		{
			trigger_error('Could not call function <i>[ Query::'.$function.' ]</i>', E_USER_ERROR);
			return false;
		}

		return call_user_func_array(array($this->_connection, $function), $args);
	}
	public function sqlQuery($sql)
	{
		$this->_sqlQuery = $sql;
		$this->exeQuery();
	}
	public function FetchObj()
	{
		return $this->_result->fetch_object();
	}
	public function FetchArr()
	{
		return $this->_result->fetch_assoc();
	}
	public function NumRows()
	{
		return $this->NumRows;
	}
	public function AffectedRows()
	{
		return $this->AffectedRows;
	}
	public function getInsertID()
	{
		return $this->_connection->insert_id;
	}
	public function Delete($from, $where = '')
	{
		if(!empty($where))
		{
			$where = ' WHERE '.$where;
		}
		$this->_sqlQuery = 'DELETE FROM '.$from.$where.';';
		$this->exeQuery();
	}
	public function listTableNames()
	{
		$this->_sqlQuery	= 'SHOW TABLES';
		$this->exeQuery();
	}
	public function countRows($table, $field = '*', $where = '')
	{
		$this->_sqlQuery	= sprintf('SELECT COUNT(%s) AS countRows FROM `%s`%s',
		($field == '*' ? '*' : '`'.$field.'`'),
		$table,
		($where == ''  ? ''  : ' WHERE '.$where));
		$this->exeQuery();
		return $this->FetchObj()->countRows;
	}

	protected function exeQuery()
	{
		try
		{
			$sql           = $this->_sqlQuery;
			$this->_result = $this->_connection->query($sql);

			if($this->_connection->errno != 0 || $this->_connection->error != 0)
			{
				$this->errno = $this->_connection->errno;
				$this->error = $this->_connection->error;
				throw new \Exception\mySQLError('Error in Query!');
			}
				
			$this->NumRows      = (!isset($this->_result->num_rows) ? 0 : $this->_result->num_rows);
			$this->AffectedRows = $this->_connection->affected_rows;
				
			\Benchmark::raiseLoadedQueries();
		}
		catch(\Exception\mySQLError $error)
		{
			echo $error->QueryError($this);
		}
	}
}
