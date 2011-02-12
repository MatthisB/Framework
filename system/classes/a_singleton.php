<?php

/**
 *
 *  Author:	Matthis
 *  Date:		11.04.2010
 *
 */

abstract class a_Singleton
{
	private static
		$instances = array();

	/**
	 * creates the instance of the called class
	 * - but normally you shouldnt use this function!
	 * -> just call \Class::Instance();
	 */
	final public static function createInstance()
	{
		$args  = func_get_args();
		$class = array_shift($args);

		if($class == null)
		{
			throw new \Exception\FatalError('Keine Klasse zum initialisieren!');
		}

		if(!array_key_exists($class, self::$instances))
		{
			$instance = & self::$instances[$class];
				
			if(count($args) < 1)
			{
				$instance = new $class();
			}
			else
			{
				$param = '';
				foreach($args as $key => $value)
				{
					$param .= '$args['.$key.'], ';
				}
				$param = substr($param, 0, -2);
				eval('$instance = new $class('.$param.');');
			}
		}
		return self::$instances[$class];
	}
	/**
	 * returns the instance of the called class
	 * - if there isnt an instance yet, create it
	 */
	public static function Instance()
	{
		try
		{
			$class  = array(get_called_class());
			$array  = array_merge($class, func_get_args());
			$return = call_user_func_array('a_Singleton::createInstance', $array);

			return $return;
		}
		catch(\Exception\FatalError $exception)
		{
			echo $exception->getMessage();
		}
	}
	/**
	 * This class should not be cloned!
	 */
	final public function __clone()
	{
		trigger_error('This singleton must not be cloned.', E_USER_ERROR);
	}
}
