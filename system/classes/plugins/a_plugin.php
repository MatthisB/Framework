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

	protected function __loadTemplate($templateName = '')
	{
		$namespace		= get_called_class();
		$namespace		= preg_replace('/^Module\\\([^\\\]+)\\\.*$/i', 'modules/$1/', $namespace);
		$namespace		= strtolower($namespace);
		$templateName	= 't_'.$templateName;

		return new \Template\HTML($templateName, $namespace);
	}
	protected function __loadTemplateCached($templateName = '', $lifetime = 3600)
	{
		$namespace		= get_called_class();
		$namespace		= preg_replace('/^Module\\\([^\\\]+)\\\.*$/i', 'modules/$1/', $namespace);
		$namespace		= strtolower($namespace);
		$templateName	= 't_'.$templateName;
			
		return new \Template\HTML_Cached($templateName, NULL, $lifetime, $namespace);
	}
}
