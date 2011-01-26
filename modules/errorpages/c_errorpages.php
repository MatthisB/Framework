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
	public function Index()
	{
		\Helper\Message::Notice('No Error found!');
	}

	public function Error404()
	{
		$this -> _siteTitle = 'Seite nicht gefunden [ Error 404 ]';

		$view  = $this->__loadView('ErrorPages');
		$view -> Error404();
	}
}
