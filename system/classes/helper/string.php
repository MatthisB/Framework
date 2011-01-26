<?php

/**
 *
 *  Author:	Matthis
 *  Date:		03.06.2010
 *
 */

namespace Helper;

class String
{
	const
		ALNUM	= 1,
		NUMERIC	= 2,
		UNIQUE	= 3;

	/**
	 * creates a random string
	 *
	 * @param	int	$type
	 * @param	int	$length
	 * @return	string
	 */
	public static function random($type = \Helper\String::ALNUM, $length = 8)
	{
		switch($type)
		{
			case self::ALNUM:
				$token = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
			case self::NUMERIC:
				$token = '0123456789';
				break;
			case self::UNIQUE:
				$token = md5(uniqid(mt_rand()));
				return substr($token, 0, $length);
				break;
			default:
				$token = '';
				trigger_error('No valide $type given!', E_USER_ERROR);
				break;
		}
		$random = '';
		for($i = 0; $i < $length; $i++)
		{
			$random .= $token{mt_rand(0, strlen($token)-1)};
		}
		return $random;
	}

	/**
	 * better use the function \Filter::mySQL_RealEscapeString directly!
	 *
	 * @param	string	$string
	 * @return	string
	 */
	public static function mySQL_RealEscapeString($string)
	{
		return \Filter::mySQL_RealEscapeString($string);
	}

	/**
	 * str_replace mit assoziativen Arrays
	 *
	 * @param	array	$replace
	 * @param	string	$string
	 * @return	string
	 */
	public static function ReplaceAssoc(array $replace, $string)
	{
		return str_replace(array_keys($replace), array_values($replace), $string);
	}

	/**
	 * stri_replace mit assoziativen Arrays
	 *
	 * @param	array	$replace
	 * @param	string	$string
	 * @return	string
	 */
	public static function iReplaceAssoc(array $replace, $string)
	{
		return str_ireplace(array_keys($replace), array_values($replace), $string);
	}

}
