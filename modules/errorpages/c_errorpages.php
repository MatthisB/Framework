<?php

/**
 *
 *  Author:	Matthis
 *  Date:		15.04.2010
 *
 */

namespace Module\ErrorPages;

class c_ErrorPages extends \MVC\a_Controller implements \MVC\i_Controller
{
	/**
	 * undefined error ... maybe wrong link called?
	 */
	public function Index()
	{
		\Helper\Message::Notice('No Error found!');
	}
	/**
	 * error 404 - file not found
	 */
	public function Error404()
	{
		$this -> _siteTitle = 'Page not found [ Error 404 ]';

		$view  = $this->__loadView('ErrorPages');
		$view -> Error404();
	}
}
