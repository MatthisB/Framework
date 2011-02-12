<?php

/**
 *
 *  Author:	Matthis
 *  Date:		01.05.2010
 *
 */

namespace Module\News;

# news script is in work

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
		echo __METHOD__.' called';

		$this->_siteTitle = 'News';
	}
	public function ReadOne()
	{
		echo __METHOD__.' called';

		$this->_siteTitle = 'News | read news ... ';
	}
}
