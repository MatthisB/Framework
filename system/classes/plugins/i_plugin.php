<?php

/**
 *
 *  Author:	Matthis
 *  Date:		22.07.2010
 *
 */

namespace Plugins;

interface i_Plugin
{
	public function __construct();

	public function runPlugin();
	public function returnContent();
}
