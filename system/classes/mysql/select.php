<?php

/**
 *
 *  Author:	Matthis
 *  Date:		18.04.2010
 *
 */

namespace mySQL;

/**
 * Usage example:
 *
 * $sql = new mySQL_Select();
 * $sql->selectFrom('DATABASE() as datenbank, NOW() as uhrzeit');
 * $sql->exeQuery();
 * echo 'Num Rows: '.$sql->NumRows."<br />";
 * echo 'Affected Rows: '.$sql->AffectedRows."<br />";
 * echo $sql->FetchObj()->jetzt;
 */

class Select extends \mySQL\Query
{
	private
		$select		= '*',
		$from		= '',
		$joins		= array(),
		$where		= '',
		$group		= '',
		$having		= '',
		$order		= '',
		$limit		= '';

	public function selectFrom($select, $from = '')
	{
		$this->select = \Filter::mySQL_RealEscapeString($select);
		$this->from   = \Filter::mySQL_RealEscapeString($from);
	}
	public function join($table, $on = '', $where = 'LEFT')
	{
		$this->joins[] = array($table, strtoupper($where), $on);
	}
	public function where($where)
	{
		$this->where = $where;
	}
	public function group($group)
	{
		$this->group = $group;
	}
	public function having($having)
	{
		$this->having = $having;
	}
	public function order($order)
	{
		$this->order = $order;
	}
	public function limit($limit)
	{
		$this->limig = $limit;
	}
	public function exeQuery()
	{
		$this->_sqlQuery  = "SELECT\n\t".$this->select."\n";

		if(!empty($this->from))
		{
			$this->_sqlQuery .= "FROM\n\t".$this->from."\n";
		}
		if(count($this->joins) >= 1)
		{
			foreach($this->joins as $join)
			{
				$this->_sqlQuery .= $join[1]." JOIN ".$join[0]."\nON\n\t".$join[2]."\n";
			}
		}
		if(!empty($this->where))
		{
			$this->_sqlQuery .= "WHERE\n\t".$this->where."\n";
		}
		if(!empty($this->group))
		{
			$this->_sqlQuery .= "GROUP BY\n\t".$this->group."\n";
		}
		if(!empty($this->having))
		{
			$this->_sqlQuery .= "HAVING\n\t".$this->having."\n";
		}
		if(!empty($this->order))
		{
			$this->_sqlQuery .= "ORDER BY\n\t".$this->order."\n";
		}
		if(!empty($this->limit))
		{
			$this->_sqlQuery .= "LIMIT\n\t".$this->limit."\n";
		}

		$this->_sqlQuery  = trim($this->_sqlQuery, "\n");
		$this->_sqlQuery .= ';';

		parent::exeQuery();
	}
}
