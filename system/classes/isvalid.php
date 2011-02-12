<?php

/**
 *
 *  Author:	Matthis
 *  Date:		19.04.2010
 *
 */

class isValid
{
	/**
	 * Returns the incoming variable and throws an error
	 *
	 * @param	string	$method
	 * @param	array	$args
	 * @return	mixed	$args[0]
	 */
	public static function __callstatic($method, $args)
	{
		trigger_error('Undefined method called in isValid ( <i>'.$method.'[ '.implode(', ', $args).' ]</i> )!', E_USER_WARNING);
		return $args[0];
	}

	/**
	 * Returns true if $var is 'true'
	 *
	 * True:
	 * 	true
	 * 	array('value')
	 * 	'yes'
	 * 	5
	 *
	 * @param	mixed	$var
	 * @return	bool
	 */
	public static function Bool($var)
	{
		if(is_array($var))
		{
			if(count($var) >= 1)
			{
				return true;
			}
			return false;
		}
		if(is_numeric($var))
		{
			if($var >= 1)
			{
				return true;
			}
			return false;
		}
		if(is_string($var))
		{
			$var    = strtolower($var);
			$values = array('true', 'yes', 'on', 'y', 'j', 'ja', 'ya');
			if(in_array($var, $values))
			{
				return true;
			}
			return false;
		}
		return ($var ? true : false);
	}

	/**
	 * Analyzes a string with different parameters
	 *
	 * True:
	 * 	isValid::String('I\'m Valid!');
	 * 	isValid::String('I\'m long enough =]', 5);
	 * 	isValid::String('I\'m longer than 5 but shorter than 100', 5, 100);
	 * 	isValid::String('I\'m shorter than 500', -1, 500);
	 *
	 * False:
	 * 	isValid::String(false);
	 * 	isValid::String(1337);
	 * 	isValid::String('Too short =(', 25);
	 *
	 * @param	mixed	$var
	 * @param	int		$minLength = -1
	 * @param	int		$maxLength = -1
	 * @return	bool
	 */
	public static function String($var, $minLength = -1, $maxLength = -1)
	{
		if(!is_string($var))
		{
			return false;
		}

		$minLength = ($minLength < 0 ? 0  : $minLength);
		$maxLength = ($maxLength < 0 ? '' : $maxLength);
		$regex	   = '/^.{'.$minLength.','.$maxLength.'}$/';

		if(preg_match($regex, $var))
		{
			return true;
		}

		return false;
	}

	/**
	 * A shorter way to validate a RegEx
	 *
	 * True:
	 * 	isValid::RegEx('123', '/[0-9]+/');
	 *
	 * False:
	 * 	isValid::RegEx('Asd', '/[0-9]+/');
	 *
	 * @param	string	$var
	 * @param	string	$regex
	 * @return	bool
	 */
	public static function RegEx($var, $regex)
	{
		if(!is_string($var) || !is_string($regex))
		{
			return false;
		}
		return (preg_match($regex, $var) !== 0);
	}

	/**
	 * Identic to is_numeric() - but maybe i'll need to add smth.
	 *
	 * @param	mixed	$var
	 * @return	bool
	 */
	public static function Numeric($var)
	{
		return (is_numeric($var));
	}

	/**
	 * Is $var a valide eMail?
	 *
	 * True:
	 * 	info@matthis-brugger.de
	 * 	true@mätthis-brügger.de
	 * 	no-reply@domain.com
	 * 	test1337@kill0r.org
	 *
	 * False:
	 * 	false@domain.ending
	 * 	info@o.com
	 *
	 * @param	mixed	$var
	 * @return	bool
	 */
	public static function Email($var)
	{
		if(!is_string($var))
		{
			return false;
		}

		$var   = strtolower($var);
		$uml   = chr(228).chr(252).chr(246);
		$regex = '/^[a-z0-9'.$uml.']+([\-_\.a-z0-9'.$uml.']+)@([a-z0-9\.\-_'.$uml.']+){2,}\.[a-z]{2,4}$/';

		return (preg_match($regex, $var) !== 0);
	}

	/**
	 * Returns true if $var is a valide URL
	 *
	 * True:
	 * 	http://www.google.de/firefox?client=firefox-a&rls=org.mozilla:de:official
	 * 	www.youtube.com/user/XildadaBumsda
	 * 	http://www.youtube.com/user/XildadadaBumsda
	 * 	http://dict.leo.org/ende?lp=ende&lang=de&searchLoc=0&cmpType=relaxed&sectHdr=on&spellToler=&search=test
	 *
	 * False:
	 * 	ht://www.google.de/firefox?client=firefox-a&rls=org.mozilla:de:official
	 * 	https://localhost/eclipse_workspace/Framework/
	 *	https://localhost/eclipse_workspace/Framework/äöü
	 * 	http://keineendung/ende?lp=ende&lang=de&searchLoc=0&cmpType=relaxed&sectHdr=on&spellToler=&search=test
	 *
	 * @param	string	$var
	 * @return	bool
	 */
	public static function URL($var)
	{
		if(!is_string($var))
		{
			return false;
		}

		$var   = strtolower($var);
		$regex = '/^(https?|ftp):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&:\/~\+#]*[\w\-\@?^=%&\/~\+#])?$/';

		if(substr($var, 0, 7) !== 'http://' && substr($var, 0, 8) !== 'https://' && substr($var, 0, 6) !== 'ftp://')
		{
			$var = 'http://'.$var;
		}

		return (preg_match($regex, $var) !== 0);
	}

	/**
	 * strpos() & stripos in one function
	 *
	 * @param	string	$search
	 * @param	string	$string
	 * @param	bool	$caseSensitive
	 * @return	bool
	 */
	public static function inString($search, $string, $caseSensitive = false)
	{
		if($caseSensitive == true && stripos($string, $search) !== false)
		{
			return true;
		}
		elseif($caseSensitive == false && strpos($string, $search) !== false)
		{
			return true;
		}

		return false;
	}

	/**
	 * are there enough / too much words ?
	 *
	 * @param	string	$string
	 * @param	int		$minWords
	 * @param	max		$maxWords
	 * @return	bool
	 */
	public static function limitWords($string, $minWords = 1, $maxWords = 100)
	{
		if(!is_string($string))
		{
			return false;
		}

		$wordCount = str_word_count($string);

		if($min <= $wordCountWords && $wordCountWords <= $max)
		{
			return true;
		}
		return false;
	}

	/**
	 * A valid Folder ( structure ) ?
	 *
	 * @param	string	$folder
	 * @param	bool	$directory
	 * @return	bool
	 */
	public static function Folder($folder, $directory = false)
	{
		if($directory === true)
		{
			$regex = '/^[a-zA-Z0-9\-\_\/.]+$/';
		}
		else
		{
			$regex = '/^[a-zA-Z0-9\-\_]+$/';
		}
		return (preg_match($regex, $folder) !== 0);
	}

	/**
	 * A valid File Name ?
	 * - with or without extension ( $ext )
	 *
	 * @param	string	$name
	 * @param	bool	$ext
	 * @return	bool
	 */
	public static function File($name, $ext = false)
	{
		if($ext === true)
		{
			$regex = '/^[a-zA-Z0-9_\-]+\.[a-zA-Z0-9]{1,4}$/';
		}
		else
		{
			$regex = '/^[a-zA-Z0-9_\-]+$/';
		}
		return (preg_match($regex, $name) !== 0);
	}

	/**
	 * A valid IP-Adress ?
	 *
	 * @param	string	$adress
	 * @return	bool
	 */
	public static function IP($address, $IPv6 = false)
	{
		if($IPv6 === true)
		{
			$regex = '/^(?:(?:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9](?::|$)){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))$/i';
		}
		else
		{
			$regex = '/^(25[0-5]|2[0-4][0-9]|[1][0-9]?[0-9]?|[1-9][1-9]?)\.((25[0-5]|2[0-4][0-9]|[1][0-9]?[0-9]?|[0-9][0-9]?)\.){2}(25[0-5]|2[0-4][0-9]|[1][0-9]?[0-9]?|[0-9][0-9]?)$/';
		}

		return (preg_match($regex, $address) !== 0);
	}

	/**
	 * Der Benutzername muss mindestens $minLength Zeichen lang sein, und höchstens $maxLength, und darf nur a-z A-Z 0-9 . _ - enthalten.
	 *
	 * @param	string	$name
	 * @param	int		$minLength
	 * @param	int		$maxLength
	 * @return	bool
	 */
	public static function NickName($name, $minLength = 3, $maxLength = 15)
	{
		$minLength = ($minLength < 0 ? 0  : $minLength);
		$maxLength = ($maxLength < 0 ? '' : $maxLength);

		$regex	   = '/^([a-zA-Z0-9\._\-]+){'.$minLength.','.$maxLength.'}$/i';

		return (preg_match($regex, $name) !== 0);
	}

	/**
	 * Must be a instance of \MVC\a_controller, \MVC\i_Controller and must contain Index()
	 * 
	 * @param	string	$controller
	 * @return	bool
	 */
	public static function ControllerObj($controller)
	{
		if($controller instanceof \MVC\a_Controller
		&& $controller instanceof \MVC\i_Controller
		&& is_callable(array($controller, 'Index')))
		{
			return true;
		}
		return false;
	}

	/**
	 * A system ID must be like a PHP variable / function but it doesn't matter if the first char is a letter or a digit.
	 * - normally it's just used intern
	 * 
	 * @param	string	$var
	 * @return	bool
	 */
	public static function systemID($var)
	{
		$regex	= '/^[0-9a-zA-Z_]+$/';
		return (preg_match($regex, $var) !== 0);
	}
	
	/**
	 * A Permission-Name ?
	 * - normally it's just used intern
	 * 
	 * @param	string	$var
	 * @param	bool	$is
	 * @return	bool
	 */
	public static function Permission($var, $is = false)
	{
		$regex	= '/^'.($is == true ? 'is' : '').'[a-zA-Z][a-zA-Z0-9_]{1,54}$/i';

		return (preg_match($regex, $var) !== 0);
	}
	
	/**
	 * A valid PHP-Function-Name?
	 * 
	 * @param	string	$var
	 * @return	bool
	 */
	public static function FunctionName($var)
	{
		$regex	= '/^[a-zA-Z][a-zA-Z0-9_]+$/i';

		return (preg_match($regex, $var) !== 0);
	}
	
	/**
	 * A valid PHP-Variable?
	 * 
	 * @param	string	$var
	 * @return	bool
	 */
	public static function VarName($var)
	{
		$regex	= '/[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/';
		return (preg_match($regex, $var) !== 0);
	}
}
