<?php

/**
 *
 *  Author:	Matthis
 *  Date:		29.05.2010
 *
 */

namespace Header;

abstract class Response implements \i_Factory
{
	const
		NORMAL		= 'Normal',
		GZIP		= 'GZip';

	public static
		$_codes	= array(200	=> 'OK',
						201	=> 'Created',
						202	=> 'Accepted',
						203	=> 'Non-Authoritative Information',
						204	=> 'No Content',
						205	=> 'Reset Content',
						206	=> 'Partial Content',

						300	=> 'Multiple Choices',
						301	=> 'Moved Permanently',
						302	=> 'Found',
						304	=> 'Not Modified',
						305	=> 'Use Proxy',
						307	=> 'Temporary Redirect',

						400	=> 'Bad Request',
						401	=> 'Unauthorized',
						403	=> 'Forbidden',
						404	=> 'Not Found',
						405	=> 'Method Not Allowed',
						406	=> 'Not Acceptable',
						407	=> 'Proxy Authentication Required',
						408	=> 'Request Timeout',
						409	=> 'Conflict',
						410	=> 'Gone',
						411	=> 'Length Required',
						412	=> 'Precondition Failed',
						413	=> 'Request Entity Too Large',
						414	=> 'Request-URI Too Long',
						415	=> 'Unsupported Media Type',
						416	=> 'Requested Range Not Satisfiable',
						417	=> 'Expectation Failed',
							
						500	=> 'Internal Server Error',
						501	=> 'Not Implemented',
						502	=> 'Bad Gateway',
						503	=> 'Service Unavailable',
						504	=> 'Gateway Timeout',
						505	=> 'HTTP Version Not Supported');

	protected
		$_status	= 'HTTP/1.1 200 OK',
		$_headers	= array();

	final public static function createClass($className)
	{
		$className = '\\Header\\Response\\'.$className;
		$fileName  = classFileName($className);
		if(!is_readable($fileName))
		{
			trigger_error('HeaderResponse Class <i>( '.$className.' )</i> does not exist!', E_USER_ERROR);
		}

		return new $className();
	}

	abstract public function getContents();
	abstract public function endBuffer();

	final public function sendRedirect($url, $now = false)
	{
		$this->setStatus('HTTP/1.1 303 See Other');
		$this->addHeader('Location', $url);
		if($now === true)
		{
			$this->__destruct();
			die();
		}
	}
	final public function setStatus($status = 'HTTP/1.1 200 OK')
	{
		$this->_status = $status;
	}
	final public function addHeader($key, $value)
	{
		$this->_headers[$key] = $value;
	}
	final public function __destruct()
	{
		header($this->_status);
		foreach($this->_headers as $header => $value)
		{
			header($header.': '.$value);
		}
		$this->endBuffer();
	}
}
