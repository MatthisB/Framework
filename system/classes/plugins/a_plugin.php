<?php

/**
 *
 *  Author:	Matthis
 *  Date:		22.07.2010
 *
 */

namespace Plugins;

class a_Plugin
{
	protected
		$_content	= '';

	/**
	 * quick access to template-class
	 * 
	 * @param	string	$templateName
	 * @return	obj
	 */
	protected function __loadTemplate($templateName = '')
	{
		$namespace		= get_called_class();
		$namespace		= preg_replace('/^Module\\\([^\\\]+)\\\.*$/i', 'modules/$1/', $namespace);
		$namespace		= strtolower($namespace);
		$templateName	= 't_'.$templateName;

		return new \Template\HTML($templateName, $namespace);
	}
	/**
	 * quick access to cached-template-class
	 * 
	 * @param	string	$templateName
	 * @param	int		$lifetime
	 * @return	obj
	 */
	protected function __loadTemplateCached($templateName = '', $lifetime = 3600)
	{
		$namespace		= get_called_class();
		$namespace		= preg_replace('/^Module\\\([^\\\]+)\\\.*$/i', 'modules/$1/', $namespace);
		$namespace		= strtolower($namespace);
		$templateName	= 't_'.$templateName;
			
		return new \Template\HTML_Cached($templateName, NULL, $lifetime, $namespace);
	}
}
