<?php

/**
  *
  *  Author:	Matthis
  *  Date:		30.08.2010
  *
  */

class Scaffold
{
	private
		$route	= '',
		$outlaw	= false;
		
	/**
	 * reads out the route of URL
	 * - then compare the route with outlaws and exceptions from routes.ini
	 * 
	 * @param	string	$controller
	 * @param	string	$action
	 */
	public function __construct($controller, $action)
	{
		$this->route	= $controller.'::'.$action;
		$exceptions		= new \iniHandler('routes', \iniHandler::READ);
		
		if(isset($exceptions->outlaws) && is_array($exceptions->outlaws))
		{
			foreach($exceptions->outlaws as $search => $replace)
			{
				if(preg_match('/'.$search.'/', $this->route))
				{
					$this->outlaw	= $this->replaceVars($replace, $controller, $action);
					return;
				}
			}
		}
		if(isset($exceptions->exceptions) && is_array($exceptions->exceptions))
		{
			foreach($exceptions->exceptions as $search => $replace)
			{
				if(preg_match('/'.$search.'/', $this->route))
				{
					$this->route	= $this->replaceVars($replace, $controller, $action);
					break;
				}
			}
		}
	}
	/**
	 * check if route is outlaw
	 */
	public function isOutlaw()
	{
		if($this->outlaw !== false)
		{
			return true;
		}
		return false;
	}
	/**
	 * run the outlaw route -> includes the called file from ./outlaws/
	 */
	public function runOutlaw()
	{
		if(substr($this->outlaw, 0, 6) != 'admin_')
		{
			$path			= ROOT.'outlaws/'.$this->outlaw.'.php';
		}
		else
		{
			$this->outlaw	= substr($this->outlaw, 6);
			$path			= ROOT.'administration/'.$this->outlaw.'.php';
		}
		
		if(is_readable($path))
		{
			include($path);
			return true;
		}
		
		trigger_error('Outlaw-File {'.$path.'} not found!', E_USER_WARNING);
		$this->Error('404');
		return false;
	}
	/**
	 * runs route, reads out meta tags and inserts all into the index template
	 */
	public function runRoute()
	{
		try
		{
			if(count($explode = explode('::', $this->route)) !== 2)
			{
				throw new \Exception\FatalError('Route <i>[ '.$this->route.' ]</i> isn\'t callable!');
			}
			
			$controller		= $explode[0];
			$method			= $explode[1];

			$controllerName	= 'Module\\'.$controller.'\c_'.$controller;
			$method			= ($method == 'NULL' ? 'Index' : $method);
			$controllerFile	= classFileName($controllerName);
			
			if(!class_exists($controllerName, false) && !is_readable($controllerFile))
			{
				trigger_error('Class {'.$controllerName.'} not found, print Error404!', E_USER_WARNING);
				$this->Error('404');
				
				return;
			}
			
			# load siteContent
			ob_start();
			
			$controllerObj	= new $controllerName();
			if(\isValid::ControllerObj($controllerName))
			{
				throw new \Exception\FatalError('Controller <i>[ '.$controllerName.' ]</i> is not a valid controller!');
			}
			
			$controllerObj -> {$method}();
			
			$siteContent	= ob_get_contents();
			ob_end_clean();
			
			# load template config
			$templateIni	= \Registry::Instance() -> templateConfig;
			$siteTemplate	= new \Template\HTML('index.html', 'templates/'.$templateIni->defaultTemplate.'/');
			
			# TRICKY: Meta Tags aus Controller laden; default Werte einfügen falls benötigt; in Template freundliches Array-Format bringen
			$metaTagsTMP = (array) $controllerObj->getMeta();
			foreach($templateIni->SEO as $key => $value)
			{
				if(!array_key_exists($key, $metaTagsTMP))
				{
					$metaTagsTMP[$key] = $value;
				}
			}
			$metaTags = array();
			foreach($metaTagsTMP as $key => $value)
			{
				$metaTags[] = array('name'	=> $key, 'content' => $value);
			}
			
			# combine data and template, then print it
			$siteTemplate -> siteContent	= $siteContent;
			$siteTemplate -> siteMeta		= $metaTags;
			$siteTemplate -> siteTitle		= sprintf($templateIni->default['title'], $controllerObj->getTitle());	
			$siteTemplate -> siteHeader		= $controllerObj->getTitle();
			
			$siteTemplate -> printTemplate();
		}
		catch(\Exception\FatalError $error)
		{
			echo $error->getMessage();
		}
	}

	/**
	 * replaces {CONTROLLER} with $controller and {ACTION} with $action
	 * - so you can use variables in routes.ini
	 * 
	 * @param	string	$str
	 * @param	string	$controller
	 * @param	string	$action
	 * @return	string
	 */
	private function replaceVars($str, $controller, $action)
	{
		$search		= array('{CONTROLLER}', '{ACTION}');
		$replace	= array($controller, $action);
		$str		= str_replace($search, $replace, $str);
		
		return $str;
	}
	/**
	 * if route doesn't exists, this function will print the error page
	 * 
	 * @param	string	$type
	 */
	private function Error($type = '404')
	{
		$errorName	 = 'Error'.$type;
		$error		 = new \Module\ErrorPages\c_ErrorPages();
		
		if(!method_exists($error, $errorName))
		{
			trigger_error('Could not find Errorfunction <i>[ '.$type.' ]</i>!', E_USER_WARNING);
			return;
		}
		
		$error		-> {$errorName}();
	}
}
