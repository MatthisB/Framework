<?php

/**
  *
  *  Author:	Matthis
  *  Date:		02.11.2010
  *
  */

namespace Email;

class Html extends \Email\a_Email
{
	public function sendEmail()
	{
		$this->checkReceiver();
		
		$this->header['MIME-Version']				= '1.0'; 
		$this->header['Content-Type']				= 'text/html; charset="UTF-8"';
		$this->header['Content-Transfer-Encoding']	= '8bit';

		return $this->_mail($this->receiver, $this->subject, $this->message, $this->headerToString());
	}
}
