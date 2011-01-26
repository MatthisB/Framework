<?php

/**
 *
 *  Author:	Matthis
 *  Date:		01.05.2010
 *
 */

namespace Module\News;

class c_News extends \MVC\a_Controller implements \MVC\i_Controller
{
	public function getTitle()
	{
		return $this->_siteTitle;
	}
	public function getMeta()
	{
		return array();
	}

	public function Index()
	{
		echo __METHOD__.' aufgerufen';

		$this->_siteTitle = 'News';
		#$this->__loadModel('test');
		#$this->__loadView('test');
	}
	public function ReadOne()
	{
		echo __METHOD__.' aufgerufen';

		$this->_siteTitle = 'News | lese news ... ';
	}
}
