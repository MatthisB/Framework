<?php

/**
  *
  *  Author:	Matthis
  *  Date:		01.02.2011
  *
  */

\Registry::Instance()->Header->addHeader('Content-Type', 'text/javascript');

switch(\Helper\URL::Instance()->_1)
{
	case 'FrameworkConfig.js':
		# print the framework constants
		echo 'FRAMEWORK_CONFIG				= {};'
			,'FRAMEWORK_CONFIG.SITEPATH		= \''.\Helper\URL::$_SITEPATH.'\';';
		break;
	
	default:
		# TODO: like outlaws/css.php - compress javascript file
		break;
}
