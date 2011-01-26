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
 * $sql  = new mySQL_Update();
 * $sql -> table('tabelle');
 * $sql -> where('1=1');
 * $sql -> limit('10');
 * $sql -> order('first');
 * $sql -> setArray(array('first' => 'first_value', 'second' => 'second_value'));
 * $sql -> third = 'third_value';
 * $sql -> exeQuery();
 */
class Update extends \mySQL\Query
{
	private
		$table = '',
		$where = '',
		$order = '',
		$limit = '';

	private
		$data  = array();

	public function __call($function, $args)
	{
		$function = strtolower($function);
		switch($function)
		{
			case 'table':
			case 'where':
			case 'order':
			case 'limit':
				$this->{$function} = $args[0];
				return true;
			default:
				return call_user_func_array(array('parent::', $function), $args);
		}
		return false;
	}
	public function __set($row, $value)
	{
		$this->data[$row] = $value;
	}
	public function setArray(array $array)
	{
		$this->data = array_merge($this->data, $array);
	}
	public function exeQuery()
	{
		try
		{
			if(empty($this->table))
			{
				throw new \Exception\mySQLError('You have to declare a Table to update it!');
			}
			if(empty($this->data))
			{
				throw new \Exception\mySQLError('You have to set some data to update!');
			}
				
			$this->_sqlQuery  = "UPDATE ".$this->table."\n";
				
			$this->_sqlQuery .= "SET\n";
			foreach($this->data as $row => $value)
			{
				if(!is_numeric($value))
				{
					$value = "'".$value."'";
				}
				$this->_sqlQuery .= "\t`".$row."` = ".$value.",\n";
			}
			$this->_sqlQuery  = trim($this->_sqlQuery, ",\n");
			$this->_sqlQuery .= "\n";
				
			if(!empty($this->where))
			{
				$this->_sqlQuery .= "WHERE\n\t".$this->where."\n";
			}
			if(!empty($this->order))
			{
				$this->_sqlQuery .= "ORDER BY ".$this->order."\n";
			}
			if(!empty($this->limit))
			{
				$this->_sqlQuery .= "LIMIT ".$this->limit."\n";
			}
				
			$this->_sqlQuery  = trim($this->_sqlQuery, "\n");
			$this->_sqlQuery .= ";";
				
			parent::exeQuery();
		}
		catch(\Exception\mySQLError $error)
		{
			$error->LogicError();
		}
	}
}
