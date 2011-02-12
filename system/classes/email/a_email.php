<?php

/**
  *
  *  Author:	Matthis
  *  Date:		01.11.2010
  *
  */

namespace Email;

abstract class a_Email
{
	protected
		$receiver		= '',
		$subject		= '( no subject )',
		$message		= '',
	
		$header			= array(); 		
				
	/**
	 * add receiver - also multiple possible
	 */
	public function addReceiver()
	{
		foreach(func_get_args() as $value)
		{
			if(\isValid::Email($value))
			{
				$this->receiver .= $value.',';
			}
		}
	}
	/**
	 * add a carbon copy - also multiple possible
	 */
	public function addCarbonCopy()
	{
		if(!array_key_exists('Cc', $this->header))
		{
			$this->header['Cc'] = '';
		}
		foreach(func_get_args() as $value)
		{
			if(\isValid::Email($value))
			{
				$this->header['Cc'] .= $value.',';
			}
		}
	}
	/**
	 * add blind carbon copy - also multiple possible
	 */
	public function addBlindCarbonCopy()
	{
		if(!array_key_exists('Bcc', $this->header))
		{
			$this->header['Bcc'] = '';
		}
		foreach(func_get_args() as $value)
		{
			if(\isValid::Email($value))
			{
				$this->header['Bcc'] .= $value.',';
			}
		}
	}
	/**
	 * set the subject of the mail
	 * 
	 * @param	string	$subject
	 */
	public function setSubject($subject)
	{
		if(!empty($subject))
		{
			if(isset(\Registry::Instance() -> frameworkConfig -> email['subject']))
			{
				$subject = sprintf(\Registry::Instance() -> frameworkConfig -> email['subject'], $subject);
			}
			$this->subject = '=?utf-8?b?'.base64_encode($subject).'?=';
		}
	}
	/**
	 * sets the content of the mail
	 * 
	 * @param	string	$message
	 */
	public function setMessage($message)
	{
		$this->message = $message;
	}
	/**
	 * set the sender mail adress - your email adress
	 * 
	 * @param	string	$from
	 */
	public function setSender($from)
	{
		$this->header['From'] = $from;
	}
	/**
	 * set the replay adress
	 * 
	 * @param	string	$reply
	 */
	public function setReply($reply)
	{
		$this->header['Reply-To'] = $reply;
	}
	/**
	 * set priority of mail - 1 highest, 5 lowest
	 * 
	 * @param	int		$priority
	 */
	public function setPriority($priority = 3)
	{
		switch($priority)
		{
			case 1:
				$priority = '1 (Highest)';
				break;
			case 2:
				$priority = '2 (High)';
				break;
			case 4:
				$priority = '4 (Low)';
				break;
			case 5:
				$priority = '5 (Lowest)';
				break;
			default:
				return true;
		}
		
		$this->header['X-Priority']	= $priority;
	}
	
	/**
	 * check if there is at least one receiver
	 */
	protected function checkReceiver()
	{
		$this->receiver = trim($this->receiver, ',');
		
		if(empty($this->receiver)
		&& (!isset($this->header['Cc']) || empty($this->header['Cc']))
		&& (!isset($this->header['Bcc']) || empty($this->header['Bcc'])))
		{
			trigger_error('You have to set at least one receiver!', E_USER_WARNING);
		}
	}
	/**
	 * transform header array to string
	 */
	protected function headerToString()
	{
		$header = '';
		foreach($this->header as $key => $value)
		{
			$value	 = trim($value, ',');
			$header .= $key.': '.$value."\n";
		}
		return $header;
	}
	/**
	 * the mail function itself - send the mail
	 * 
	 * @param	string	$to
	 * @param	string	$subject
	 * @param	string	$message
	 * @param	array	$additional_headers
	 * @return	bool
	 */
	protected function _mail($to, $subject, $message, $additional_headers = array())
	{
		if(!isset($this->header['From']) && isset(\Registry::Instance() -> frameworkConfig -> email['From']))
		{
			$this->header['From']	= \Registry::Instance() -> frameworkConfig -> email['From'];
		}
		if(!isset($this->header['Reply-To']) && isset(\Registry::Instance() -> frameworkConfig -> email['Reply-To']))
		{
			$this->header['Reply-To']	= \Registry::Instance() -> frameworkConfig -> email['Reply-To'];
		}

		return mail($to, $subject, $message, $additional_headers);
	}
}
