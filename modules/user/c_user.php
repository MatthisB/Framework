<?php

/**
  *
  *  Author:	Matthis
  *  Date:		20.09.2010
  *
  */

namespace Module\User;

class c_User extends \MVC\a_Controller implements \MVC\i_Controller
{
	/**
	 * TODO: maybe own user profile?
	 */
	public function Index()
	{
		\Helper\Message::Notice('Something went wrong ... maybe you followed a broken link ...');
		echo \Helper\HTML::redirectJS(\Helper\URL::$_SITEPATH, 3);
	}
	/**
	 * resend the activation mail to user
	 */
	public function resendactivationmail()
	{
		$this->_siteTitle = 'User Activation';
		
		try
		{
			if(LOGGEDIN)
			{
				throw new \Exception\NormalError('You are already logged in!');
			}
			
			$avoidspam = new \Helper\AvoidSpam('1 HOUR', 'activationmail');
			if($avoidspam -> CheckHits() !== false)
			{
				throw new \Exception\NormalError('Activationmail has already been send.');
			}
			
			if(!isset(\Helper\URL::Instance()->_2)
			|| !\isValid::Numeric(\Helper\URL::Instance()->_2))
			{
				throw new \Exception\NormalError('No or wrong UserID given - wrong request!');
			}
			
			$activationObj = $this->__loadModel('Register')->getActivationObj(\Helper\URL::Instance()->_2);
			if($activationObj -> activationDate != '0000-00-00 00:00:00')
			{
				throw new \Exception\NormalError('Your account had been already activated!');
			}
			
			$this->__loadView('Register') -> sendActivationMail($activationObj -> ID, $activationObj -> nick, $activationObj -> email, $activationObj -> hash);
			$this->__loadView('Register') -> reSendActivationMail();
			
			$avoidspam -> Insert();
		}
		catch(\Exception\NormalError $error)
		{
			echo $error -> getErrorMessage();
		}
	}
	/*
	 * activate the user account if security hash is correct
	 */
	public function activation()
	{
		$this->_siteTitle = 'User Activation';
		
		try
		{
			if(LOGGEDIN)
			{
				throw new \Exception\NormalError('You already have an user account!');
			}	
			if(!isset(\Helper\URL::Instance()->_2)
			|| !\isValid::Numeric(\Helper\URL::Instance()->_2))
			{
				throw new \Exception\NormalError('No or wrong UserID given - wrong request!');
			}
			
			$activationObj = $this->__loadModel('Register')->getActivationObj(\Helper\URL::Instance()->_2);
			
			
			if($activationObj -> activationDate != '0000-00-00 00:00:00')
			{
				throw new \Exception\NormalError('Your account had been already activated!');
			}
			if($activationObj -> hash != \Helper\URL::Instance()->_3)
			{
				throw new \Exception\NormalError('The security-hash is invalid!');
			}
			
			$this->__loadModel('Register')->activateUser(\Helper\URL::Instance()->_2);			
			$this->__loadView('Register')->successActivation();
		}
		catch(\Exception\NormalError $error)
		{
			echo $error -> getErrorMessage();
		}
	}
	/**
	 * register a new user
	 */
	public function register()
	{
		$this->_siteTitle = 'Registration Form';
		
		if(LOGGEDIN)
		{
			\Helper\Message::Error('You already have an user account!');
			return;
		}	
		
		if(isset($this->_POST->submit))
		{
			try
			{
				$m_User	= $this->__loadModel('User');
				$errors	= array();
				
				if(!isset($this->_POST->captchaID))
				{
					throw new \Exception\NormalError('Necessary value <i>( captchaID )</i> isn\'t given.');
				}

				$captcha  = new \Captcha\Controller($this->_POST->captchaID);	
				if($captcha -> checkSession($this->_POST->captchaValue) !== true)
				{
					$errors['captchaValue']	= 'The entered Security-Code wasn\'t correct!';
				}
				$captcha -> deleteSession();
					
				if(!isset($this->_POST->user->nick)
				|| \isValid::NickName($this->_POST->user->nick) !== true)
				{
					$errors['user[nick]']	= 'The entered nickname isn\'t valid!';
				}
				elseif($m_User->nickAlreadyInUse($this->_POST->user->nick))
				{
					$errors['user[nick]']	= 'The entered nickname is already in use!';
				}
				
				if(!isset($this->_POST->user->email)
				|| \isValid::Email($this->_POST->user->email) !== true)
				{
					$errors['user[email]']	= 'The entered email adress isn\'t valid!';
				}
				elseif($m_User->emailAlreadyInUse($this->_POST->user->email))
				{
					$errors['user[email]']	= 'The entered email adress is already in use!';
				}
				
				if(!isset($this->_POST->password->_1, $this->_POST->password->_2)
				|| \isValid::String($this->_POST->password->_1, 5) !== true
				|| $this->_POST->password->_1 !== $this->_POST->password->_2)
				{
					$errors['password[1]']	= 'Your password must be longer than 5 letters and the repetition must match!';
				}
				
				if(!empty($errors))
				{
					throw new \Exception\NormalError($errors);
				}
				
				$userID   = $this->__loadModel('Register') -> insertUser($this->_POST->user->nick, $this->_POST->user->email);
				$m_User  -> setPassword($userID, $this->_POST->password->_1);
				
				# create and send activation-mail to user
				$hash     = md5($userID.\Helper\Login::createPwHash($this->_POST->password->_1));		
				$this->__loadView('Register') -> sendActivationMail($userID, $this->_POST->user->nick, $this->_POST->user->email, $hash);

				# show success-message
				$view     = $this->__loadView('Register') -> successRegister($this->_POST->user->nick, $this->_POST->user->email);
			}
			catch(\Exception\NormalError $error)
			{
				echo $error -> getErrorMessage();
				echo 'Please try again!';
				
				$errorFields = array_keys($error -> getGenuineMessage());
				
				$this->__loadView('Register') -> showForm($this->_POST->user->nick, $this->_POST->user->email, $errorFields);
			}
		}
		else
		{
			$this->__loadView('Register') -> showForm();
		}
	}
}
