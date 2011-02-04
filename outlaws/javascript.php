<?php

/**
  *
  *  Author:	Matthis
  *  Date:		01.02.2011
  *
  */

# TODO: wenn \Helper\URL::Instance()->_2 im javascript ordner exisitert komprimiert und gecached ausgeben

\Registry::Instance()->Header->addHeader('Content-Type', 'application/javascript');

switch(\Helper\URL::Instance()->_1)
{
	case 'FrameworkConfig.js':
		echo 'FRAMEWORK_CONFIG				= {};'
			,'FRAMEWORK_CONFIG.SITEPATH		= \''.\Helper\URL::$_SITEPATH.'\';';
		break;
}
