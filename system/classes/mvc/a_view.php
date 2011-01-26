<?php

/**
 *
 *  Author:	Matthis
 *  Date:		21.07.2010
 *
 */

namespace MVC;

abstract class a_View
{
	protected function __loadTemplate($templateName)
	{
		$namespace		= get_called_class();
		$namespace		= preg_replace('/^Module\\\([^\\\]+)\\\.*$/i', 'modules/$1/', $namespace);
		$namespace		= strtolower($namespace);
		$templateName	= 't_'.$templateName;

		return new \Template($templateName, $namespace);
	}
	protected function __loadTemplateCached($templateName, $lifetime = 3600)
	{
		$namespace		= get_called_class();
		$namespace		= preg_replace('/^Module\\\([^\\\]+)\\\.*$/i', 'modules/$1/', $namespace);
		$namespace		= strtolower($namespace);
		$templateName	= 't_'.$templateName;
			
		return new \TemplateCached($templateName, NULL, $lifetime, $namespace);
	}
}
