<?php

/**
 *
 *  Author:	Matthis
 *  Date:		12.07.2010
 *
 */

namespace Module\ErrorPages;

class v_ErrorPages extends \MVC\a_View implements \MVC\i_View
{
	/**
	 * display 404 error page
	 */
	public function Error404()
	{
		$template  = $this->__loadTemplate('error404.html');
		$template -> seite = '['.\Header\Request::getMethod().'] '.\Header\Request::getURI();
		$template -> referer = \Header\Request::getReferer();
		$template -> printTemplate();
	}
}
