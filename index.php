<?php
/**
 *
 *  Author:	Matthis
 *  Date:		29.05.2010
 *
 */

define('ROOT', 			realpath(dirname(__FILE__)).'/');	# Root Ordner
define('DEVELOPMENT',	true);								# Fehlerbehandlung

ErrorReporting();


# Header stuff
\Registry::Instance()->Header  = \Header\Response::createClass(\Header\Response::NORMAL);
\Registry::Instance()->Header -> addHeader('Content-Type', 'text/html; charset=utf-8');


# Benchmark and stuff
\Registry::Instance()->Benchmark  = \Benchmark::Instance();
\Registry::Instance()->Benchmark -> startTimer('wholeSite');


# Entfernt falls magic_quotes aktiviert sind die backslashes
\Header\Request::removeMagicQuotes();


# Initialize URL Helper
\Helper\URL::Instance();


# mySQL stuff
$mySQL       = new \mySQL\Connection();
\Registry::Instance()->mySQL_Standard_Connection = $mySQL -> getConnection();

$query		 = new \mySQL\Query();
$query		-> sqlQuery('set names "utf8";');

define('PREFIX', \Registry::Instance()->mySQL_Standard_Data->prefix);


# Session and Cookies stuff
\Session\SaveHandler::Instance();
\Session\Scope::Instance();
\Cookie\Purge::Instance();
\Helper\Token::Instance();


# Initialize Login, set permissions
\Helper\Login::Instance();
\Helper\Permissions::Instance();

define('LOGGEDIN', \Helper\Login::isLoggedIn());


# Just a few design & template things
define('TEMPLATE_DIR', ROOT.'templates/'.\Registry::Instance() -> templateConfig -> defaultTemplate.'/');



/**
 * Bestimmt was mit Fehlern angestellt wird
 * @return	void
 */
function ErrorReporting()
{
	error_reporting(E_ALL | E_STRICT | E_NOTICE);
	if(DEVELOPMENT == true)
	{
		ini_set('display_errors',	'On');
	}
	else
	{
		ini_set('display_errors',	'Off');
		ini_set('log_errors',		'On');
		ini_set('error_log',		ROOT.'/system/files/logs/php_error.log');
	}
}

/**
 * Klassen automatisch bei Benutzung laden
 *
 * @param	string	$className
 * @return	void
 */
function __autoload($className)
{
	if(!preg_match('/^[a-zA-Z\\\][a-zA-z_0-9\\\]+[^_\\\]$/', $className))
	{
		trigger_error('Classname ( <i>'.$className.'</i> ) isn\'t valide!', E_USER_ERROR);
	}
	
	$file = ROOT.classFileName($className);
	
	if(!file_exists($file))
	{
		trigger_error('File <i>'.$file.'</i> not found!', E_USER_ERROR);
	}
		
	include_once($file);
	
	if(!class_exists($className) && !interface_exists($className))
	{
		trigger_error('Class or Interface <i>'.$className.'</i> not found!', E_USER_ERROR);
	}

	\Benchmark::raiseLoadedClasses();
}

/**
 * Generiert die Datei Namen der Klassen die über __autoload() aufgerufen werden
 *
 * @param	string	$className
 * @return	string	$file		Pfad zur Klassen-Datei
 */
function classFileName($className)
{
	if(stripos(substr($className, 0, 7), 'Module\\') !== false)
	{
		$file      = 'modules/';
		$className = substr($className, 7);
	}
	else
	{
		$file  = 'system/classes/';
	}

	$exp   = explode('\\', $className);

	while(sizeof($exp) > 1)
	{
		$file .= array_shift($exp).'/';
	}

	$file .= array_shift($exp).'.php';
	$file  = strtolower($file);

	return $file;
}

/**
 * Hilfsfunktion, um globale Variablen ( $_GET, $_POST ... ) auf existenz und Inhalt zu testen
 * Vorsicht; falls nicht gesetzt / leer liefert die funktion true - evtl. ab und zu etwas verwirrend
 * 	0 = GET, 1 = POST, 2 = REQUEST, 3 = SESSION, 4 = COOKIE
 *
 * @param	mixed	$var
 * @param	int		$request = 0
 * @return	bool
 */
function isEmpty($var, $request = 0)
{
	switch($request)
	{
		case 0:
			return (!isset($_GET[$var])		|| empty($_GET[$var]));
		case 1:
			return (!isset($_POST[$var])	|| empty($_POST[$var]));
		case 2:
			return (!isset($_REQUEST[$var])	|| empty($_REQUEST[$var]));
		case 3:
			return (!isset($_SESSION[$var])	|| empty($_SESSION[$var]));
		case 4:
			return (!isset($_COOKIE[$var])	|| empty($_COOKIE[$var]));
		default:
			trigger_error('isEmpty::$request <i>[ '.$request.' ]</i> isn\'t correctly defined!', E_USER_WARNING);
			return false;
	}
}

/**
 * varDump - mit <pre>
 *
 * @param	mixed
 */
function varDump()
{
	echo '<pre>';
	call_user_func_array('var_dump', func_get_args());
	echo '</pre>';
}



# Load and Run Page Core
$controller	= \Helper\URL::Instance()->_class;
$action		= \Helper\URL::Instance()->_method;

$scaffold	= new \Scaffold($controller, $action);
if($scaffold -> isOutlaw())
{
	$scaffold -> runOutlaw();
}
else
{
	$scaffold -> runRoute();
}



# Just some BBCode tests here!

/*
$start = <<<'ANFANG'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!--
  
  Author:	Matthis
  Date:		01.06.2010

-->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{$siteTitle}</title>
<link rel="stylesheet" type="text/css" href="http://localhost/eclipse_workspace/Framework/templates/basicStyles.css" />   
<script type="text/javascript" src="http://localhost/eclipse_workspace/Framework/templates/basicClasses.js"></script>
<script type="text/javascript" src="http://localhost/eclipse_workspace/Framework/templates/basicFunctions.js"></script>
</head>
<body>
ANFANG;
echo $start;

$string1 = "[noparse]Das ist ein [b]text[/b]![/noparse]
[size expression(alert(document.cookie));]Size tag[/size]
[font expression(alert(document.cookie));]Font tag[/font]
[float expression(alert(document.cookie));]Float tag[/float]
[url javascript:alert(document.cookie)]Click here to see cookie[/url]
[anchor javascript:alert(document.cookie)]Anchor[/anchor]
[img]javascript:document.location='http://www.albinoblacksheep.com/flash/you.html'[/img]
[img]javascript:alert('XSS')[/img]";

$string2	 = 
"[noparse asd asdasd='asdasd']
Das ist ein
 [b]text[/b]!
 [/noparse]

\n
 
 
[size 5]Size tag[/size]
[align center]mittig![/align]
[align asd]mittig![/align]
[pre]code bla und so - versuch mit [b]dick[/b] ...[/pre]
[color ffffff]weiß[/color][color FF00FF]farbe[/color][color nixasd][b]keine[/b] farbe[/color]
[img Alt-Text]http://www.giftler.de/v4/bilder/login_bg.png[/img]
[img <asd>]bild.png[/img]
[url=http://www.phpbb.com/]Besucht phpBB![/url]
[url]http://www.phpbb.com/[/url]
[email]info@matthis-brugger.de[/email]
[email=mobile@matthis-brugger.de]meine mobile adresse[/email]
[hr]
[quote=Matthis]das ist ein Text![/quote]
[quote=Matthis1]das ist ein text![quote=Matthis2]das war ein text[quote]das ist ein text gewesen[/quote][/quote][/quote]

[br]

[url]
http://www.phpbb.com/[/url]
[php]
<?php

class a
{
	public function isRed(Colors \$const)
	{
		switch(\$const)
		{
			case Colors::GREEN:
				echo 'isGreen';
				break;
			case Colors::RED:
				echo 'isRed, ';
				break;
		}
		
		
		if(\$const == Colors::RED)
			echo 'JUHU';
	}
}

\$a	 = new a();
\$a -> isRed(new Colors(Colors::RED));

?>
[/php]
[code]
SELECT
	test
FROM
	tabelle
WHERE
	test = 'test';
[/code]

[table]
[tr]
	[td]1.1[/td]
	[td]1.2[/td]
	[td]1.3[/td]
[/tr]
[tr]
	[td]2.1[/td]
	[td]2.2[/td]
	[td]2.3[/td]
[/tr]
[tr]
	[td]3.1[/td]
	[td]3.2[/td]
	[td]3.3[/td]
[/tr]
[/table]


[list]
	[*]1.1
	[*]1.2
	[*][list a]
	[*]1.3.1
	[*]1.3.2
	[/list]
	[*]1.4
[/list]

[list]
	[*]1.1
	[*]1.2
	[*][list a]
	[*]1.3.1
	[*]1.3.2
	[/list]
	[*]1.4
[/list]


[list]
	[*]1.1
	[*]1.2
	[*][list a]
	[*]1.3.1
	[*]1.3.2
	[/list]
	[*]1.4
[/list]
";

#varDump(preg_match('/\[(\w+)((?:\s|=)[^]]*)?\]((?:[^[]|\[(?!\/?\1((?:\s|=)[^]]*)?\])|(?R))+)\[\/\1\]/', $string2, $array), $array);
#die();

$string		 = $string2;

$bbcode	 = new \Helper\BBCode($string);
$bbcode	 = $bbcode -> parseBBCode();

echo $string."\n\n<br /><hr /><br />\n\n".$bbcode;

echo '</body></html>';
*/