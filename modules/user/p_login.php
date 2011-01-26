<?php

/**
 *
 *  Author:	Matthis
 *  Date:		22.07.2010
 *
 */

namespace Module\User;

class p_Login extends \Plugins\a_Plugin implements \Plugins\i_Plugin
{
	public function __construct()
	{

	}
	public function runPlugin()
	{
		if(!LOGGEDIN)
		{
			$this->_content	= $this->__loadTemplate('loginform') -> returnTemplate();
			return;
		}
		
		$this->_content = 'Hi '.\Session\Scope::Instance() -> user -> Nick.', you\'re logged in! =)<br />'
						. 'Your ID is #'.\Session\Scope::Instance() -> user -> ID.".<br />\n"
						. '<a href="'.\Helper\URL::$_SITEPATH.'logout/">Logout</a>'."\n";
	}
	public function returnContent()
	{
		return $this->_content;
	}
}
