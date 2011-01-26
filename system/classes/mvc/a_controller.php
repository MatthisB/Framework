<?php

/**
 *
 *  Author:	Matthis
 *  Date:		14.04.2010
 *
 */

namespace MVC;

abstract class a_Controller
{
	protected
		$_POST		= array(),
		
		$_siteTitle = '',
		$_siteMeta	= array();
		
	public function __construct()
	{
		$this->_POST = new \Helper\InputObject(\Helper\InputObject::POST);
	}
	public function __call($method, $value)
	{
		\Helper\Message::Error('Could not find called action <i>[ '.$method.' ]</i>!');
		$this->Index();
	}

	public function getTitle()
	{
		return $this->_siteTitle;
	}
	public function getMeta()
	{
		return $this->_siteMeta;
	}
	
	protected function __loadView($view)
	{
		$view	= preg_replace('/^\\\?Module\\\(.*)\\\c_.*$/i', 'Module\\\$1\v_'.$view, get_called_class());

		return new $view();
	}
	protected function __loadModel($model)
	{
		$model	= preg_replace('/^\\\?Module\\\(.*)\\\c_.*$/i', 'Module\\\$1\m_'.$model, get_called_class());

		return new $model();
	}
}
