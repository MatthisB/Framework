<?php

/**
  *
  *  Author:	Matthis
  *  Date:		01.11.2010
  *
  */

namespace Email;

class Attachment extends \Email\a_Email
{
	private
		$attachments	= array();
	
	/**
	 * add multiple attachments
	 */
	public function addAttachment()
	{
		foreach(func_get_args() as $key => $value)
		{
			$this->attachments[] = $value;
		}
	}
	/**
	 * convert the attachments to string and prepare for transmit
	 */
	public function sendEmail()
	{
		$this->checkReceiver();
		
		$content		= $this->message;
		$boundary		= '-----='.\Helper\String::random(\Helper\String::UNIQUE, 32);

		$this->message	 = '--'.$boundary."\n";
		$this->message	.= "Content-Type: text/html; charset=UTF-8\n";
		$this->message	.= "Content-Transfer-Encoding: base64\n\n";
		$this->message	.= chunk_split(base64_encode($content))."\n\n";
		
		if(empty($this->attachments))
		{
			trigger_error('There should be at least one attachment!', E_USER_WARNING);
		}
		foreach($this->attachments as $file)
		{			
			if(($file = realpath($file)) === false)
			{
				continue;
			}
			$basename	= basename($file);
			
			$this->message .= '--'.$boundary."\n";
			$this->message .= "Content-Type: ".mime_content_type($file)."; name=\"".$basename."\"\n";
			$this->message .= "Content-Transfer-Encoding: base64\n";
			$this->message .= "Content-Disposition: attachment; filename=\"".$basename."\"\n\n";
			$this->message .= chunk_split(base64_encode(file_get_contents($file)))."\n";

			$this->message .= '--'.$boundary."\n\n";
		}
				
		$this->header['MIME-Version']				= '1.0'; 
		$this->header['Content-Type']				= 'multipart/mixed; boundary="'.$boundary.'"';	
		$this->header['Content-Transfer-Encoding']	= '8bit';

		return $this->_mail($this->receiver, $this->subject, $this->message, $this->headerToString());
	}
}
