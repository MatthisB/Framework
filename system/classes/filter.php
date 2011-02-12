<?php

/**
 *
 *  Author:	Matthis
 *  Date:		31.05.2010
 *
 */

class Filter
{
	/**
	 * Identic to intval() - but maybe i'll need to add smth.
	 *
	 * @param	mixed	$var
	 * @return	bool
	 */
	public static function Int($var)
	{
		return intval($var);
	}
	
	/**
	 * Deletes all invalid chars etc. in the fileName
	 * 
	 * @param	string	$fileName
	 * @param	bool	$ext
	 * @return	bool
	 */
	public static function File($fileName, $ext = false)
	{
		$regex = ($ext === true ? '/[^a-zA-Z0-9_\-.]/' : '/[^a-zA-Z0-9_\-]/');

		return preg_replace($regex, '', $fileName);
	}

	/**
	 * Deletes all invalid chars etc. in the folderName or directory
	 * 
	 * @param	string	$folderName
	 * @param	bool	$directory
	 * @return	bool
	 */
	public static function Folder($folderName, $directory = false)
	{
		$regex = ($directory == true ? '/[^a-zA-Z0-9\-\_\/\\\.:]/' : '/[^a-zA-Z0-9\-\_]/');
		
		return preg_replace($regex, '', $folderName);
	}
	
	/**
	 * Deletes all invalid chars in the systemID
	 * 
	 * @param	string	$var
	 * @return	bool
	 */
	public static function systemID($var)
	{
		return preg_replace('/[^0-9a-zA-Z_]/', '', $var);
	}
	
	/**
	 * Cuts off a string by the word-limit
	 * - attachs $endChar
	 * 
	 * @param	string	$var
	 * @param	int		$limit
	 * @param	string	$endChar
	 * @return	bool
	 */
	public static function limitWords($var, $limit, $endChar = '&#8230;')
	{
		if(preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $var, $matches)
		&& strlen($var) != strlen($matches[0]))
		{
			$var = rtrim($matches[0]).$endChar;
		}
			
		return $var;
	}
	
	/**
	 * easier and more comfortable version of mysqli_real_escape_string
	 *
	 * @param	string	$string
	 * @return	string
	 */
	public static function mySQL_RealEscapeString($string)
	{
		return mysqli_real_escape_string(\Registry::Instance()->mySQL_Standard_Connection, $string);
	}

	/**
	 * ensures a string against xss attacks
	 * 
	 * @param	string	$string
	 * @return	string
	 */
	public static function XSS_EscapeString($string)
	{
		return htmlentities($string, ENT_QUOTES, 'UTF-8', false);
	}

	/**
	 * prepares a message for the log classes
	 * 
	 * @param	string	$string
	 * @return	string
	 */
	public static function LogMessage($string)
	{
		$string	 = strip_tags($string);
		$string	 = str_replace(array("\n", "\r"), "\t", $string);
		
		return $string;
	}
}
