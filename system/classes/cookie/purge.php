<?php

/**
 *
 *  Author:	Matthis
 *  Date:		05.07.2010
 *
 */

namespace Cookie;

class Purge extends \a_Singleton
{
	/**
	 * cleans up the expired cookies
	 */
	protected function __construct()
	{
		$sql  = new \mySQL\Query();
		$sql -> Delete(PREFIX.'cookies', 'expireDate < NOW()');
	}
}
