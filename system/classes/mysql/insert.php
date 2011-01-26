<?php

/**
 *
 *  Author:	Matthis
 *  Date:		21.04.2010
 *
 */

namespace mySQL;

/**
 * Usage example:
 *
 * $sql  = new mySQL_Insert();
 * $sql -> table('testtable');
 * $sql -> setCols(array('col1', 'col2', 'col3'));
 * $sql -> setStack(array('inhalt 1.1', 'inhalt 1.2', 'inhalt 1.3'));
 * $sql -> setStack(array('inhalt 2.1', 'inhalt 2.2'));					# skip & error
 * $sql -> setStack(array('inhalt 3.1', 'inhalt 3.2', 'inhalt 3.3'));
 * $sql -> exeQuery();
 *
 */
class Insert extends \mySQL\Query
{
	private
		$table  = '',
		$stacks = array(),
		$cols   = array();

	public function table($name)
	{
		$this->table = $name;
	}
	public function setCols(array $cols)
	{
		$this->cols = $cols;
	}
	public function setStack(array $array)
	{
		$this->stacks[] = $array;
	}
	public function exeQuery()
	{
		try
		{
			if(empty($this->table))
			{
				throw new \Exception\mySQLError('You have to declare a Table to update it!');
			}
			if(empty($this->stacks))
			{
				throw new \Exception\mySQLError('You have to set some data to insert!');
			}
			if(empty($this->cols))
			{
				throw new \Exception\mySQLError('You have to set some columns where data should be insert!');
			}
				
			$this->_sqlQuery  = "INSERT INTO ".$this->table." (\n";
				
			foreach($this->cols as $col)
			{
				$this->_sqlQuery .= "\t`".$col."`,\n";
			}
			$this->_sqlQuery  = trim($this->_sqlQuery, "\n,");
			$this->_sqlQuery .= ")\nVALUES\n";
				
			$count_cols = count($this->cols);
			foreach($this->stacks as $stack)
			{
				if(count($stack) != $count_cols)
				{
					trigger_error('Skipped stack to insert; count(stack) != count(columns)', E_USER_NOTICE);
					continue;
				}

				$this->_sqlQuery .= "\t(";
				foreach($stack as $value)
				{
					if(!is_numeric($value))
					{
						$value = "'".$value."'";
					}
					$this->_sqlQuery .= $value.', ';
				}
				$this->_sqlQuery  = trim($this->_sqlQuery, ', ');
				$this->_sqlQuery .= "),\n";
			}
			$this->_sqlQuery  = trim($this->_sqlQuery, ",\n");
			$this->_sqlQuery .= "\n";
			$this->_sqlQuery .= ";";

			parent::exeQuery();
		}
		catch(\Exception\mySQLError $error)
		{
			$error->LogicError();
		}
	}
}
