<?php

/**
  *
  *  Author:	Matthis
  *  Date:		18.10.2010
  *
  */

namespace Module\User;

class v_Register extends \MVC\a_View implements \MVC\i_View
{
	public function showForm($nick = '', $email = '', $errorFields = array())
	{
		$template	 = $this->__loadTemplate('register_form.html');
		$template	-> nick		= \Filter::XSS_EscapeString($nick);
		$template	-> email	= \Filter::XSS_EscapeString($email);

		$template	-> FormField_AddClass($errorFields, 'formError');

		$template	-> printTemplate();
	}
	public function successRegister($nick, $email)
	{
		$template  = $this->__loadTemplate('register_success.html');

		$template -> nick	= $nick;
		$template -> email	= $email;
		
		$template -> printTemplate();
	}
	
	
	public function reSendActivationMail()
	{
		\Helper\Message::Success('Your activationmail has been send again ...<br />Please check your mailbox.');
	}
	public function sendActivationMail($userID, $nick, $email, $hash)
	{
		$subject  = 'Registration Activation';
		$body	  = 'Hey '.$nick.',<br />'
				  . 'to complete your activation you have to click here: <a href="'.\Registry::Instance() -> frameworkConfig -> sitePath.'user/activation/'.$userID.'/'.$hash.'/">Activation Link</a>.';
		
		$message  = new \Template('email_wrapper.html', TEMPLATE_DIR);
		$message -> subject	= $subject;
		$message -> body	= $body;
		$message  = $message -> returnTemplate();
		
		$mail     = new \Email\Html();
		$mail    -> addReceiver($email);
		$mail    -> setSubject($subject);
		$mail    -> setMessage($message);
		$mail    -> sendEmail();
	}
	public function successActivation()
	{
		\Helper\Message::Success('Congratulations, you\'ve done it ...<br />You can login now.');
	}
}
