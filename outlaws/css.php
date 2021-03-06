<?php

/**
  *
  *  Author:	Matthis
  *  Date:		03.02.2011
  *
  */


$cacheLifetime	 = (isset(\Registry::Instance() -> templateConfig -> cssCacheLifetime) && \isValid::Numeric(\Registry::Instance() -> templateConfig -> cssCacheLifetime) ? \Registry::Instance() -> templateConfig -> cssCacheLifetime : 60*60*3);

# save parsed css file intto browser cache
\Registry::Instance()->Header->addHeader('Content-Type', 'text/css');
\Registry::Instance()->Header->addHeader('Cache-Control', $cacheLifetime);
\Registry::Instance()->Header->addHeader('Expires', gmdate('D, d M Y H:i:s', time()+$cacheLifetime).' GMT');

# get called css-file-name ( complete path )
$cssFile	 = str_replace(\Helper\URL::$_SITEPATH.'css/', '', \Helper\URL::$_CURRENT);
$cssFile	 = substr($cssFile, 0, -1);

# run class
$cssObj		 = new \Template\CSS($cssFile, $cacheLifetime);
$cssObj		-> runTemplate();
$cssContent	 = $cssObj -> returnTemplate();

echo $cssContent;
