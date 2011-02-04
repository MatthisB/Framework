<?php

/**
 *
 *  Author:	Matthis
 *  Date:		27.07.2010
 *
 */

class LoginLogout
{
	private
		$template	= NULL;
		
	public function __construct()
	{
		$this->template	= new \Template\HTML('login.html', TEMPLATE_DIR);
		
		try
		{
			if(\Helper\URL::Instance()->_class == 'login')
			{
				$this->runLogin();
			}
			else
			{
				$this->runLogout();
			}
		}
		catch(\Exception\NormalError $error)
		{
			$this -> template -> message	= \Helper\Message::Error($error -> getMessage(), ucfirst(\Helper\URL::Instance()->_class).' Failed', true);
			$this -> template -> form		= $error -> showForm;
			$this -> template -> printTemplate();
		}
	}
	
	private function runLogin()
	{
		if(LOGGEDIN)
		{
			throw new \Exception\NormalError(\Session\Scope::Instance() -> user['Nick'].', you\'re already loggedin!', array('showForm' => false));
		}
		
		$logFails	= new \Helper\AvoidSpam(\Registry::Instance() -> frameworkConfig -> login['lockTime'], 'login');

		if($logFails -> CheckHits() + 1 > \Registry::Instance() -> frameworkConfig -> login['maxFails'])
		{
			throw new \Exception\NormalError('You have too many failed attempts!', array('showForm' => false));
		}
		
		if(isEmpty('loginName', 1) || isEmpty('loginPass', 1))
		{
			throw new \Exception\NormalError('You have to fill in both fields!', array('showForm' => true));
		}
		
		$postName	= \Filter::mySQL_RealEscapeString($_POST['loginName']);
		$postPass	= \Filter::mySQL_RealEscapeString($_POST['loginPass']);

		$userID		= \Helper\User::getUserIDbyNick($postName);
		$pwHash		= \Helper\Login::createPwHash($postPass);

		if($userID !== false && ($activated = \Helper\Login::checkLogin($userID, $pwHash)) !== false)
		{
			$logFails -> Delete();
			
			if($activated === -1)
			{
				throw new \Exception\NormalError('You have to activate your account first!<br />Click <a href="'.\Helper\URL::$_SITEPATH.'user/resendactivationmail/'.$userID.'/">here</a> if you haven\'t received the activation mail.', array('showForm' => false));
			}
			
			session_regenerate_id(false);

			$cookie  = new \Cookie\Helper('fw_login');
			$cookie -> create(\Registry::Instance() -> frameworkConfig -> login['cookieDurability']);
			$cookie -> userID	= $userID;
			$cookie -> pwHash	= $pwHash;
			
			\Session\Scope::Instance() -> user	= new \ArrayObject();
			\Session\Scope::Instance() -> user	= \Helper\User::getUserData($userID);

			$this -> template -> message = \Helper\Message::Success('Welcome back '.\Session\Scope::Instance()->user->Nick.'.', '', true)
										 . \Helper\HTML::redirectJS(\Helper\URL::$_SITEPATH, 3);

			$this -> template -> form	 = false;
			$this -> template -> printTemplate();
					
			return true;
		}
		else
		{
			$logFails -> Insert();
	
			throw new \Exception\NormalError('That was your '.$logFails -> CheckHits().'/'.\Registry::Instance() -> frameworkConfig -> login['maxFails'].' try!', array('showForm' => true));
		}	
	}
	private function runLogout()
	{
		if(!LOGGEDIN)
		{
			throw new \Exception\NormalError('You can\'t log out, cause you\'re not logged in!', array('showForm' => false));
		}
		
		$nick	 = \Session\Scope::Instance() -> user -> Nick;
		
		$cookie  = new \Cookie\Helper('fw_login');
		$cookie -> delete();
					
		unset(\Session\Scope::Instance() -> user);
		
		$this -> template -> message = \Helper\Message::Success('Bye bye '.$nick.' ...', '', true)
									 . \Helper\HTML::redirectJS(\Helper\URL::$_SITEPATH, 3);
									 
		$this -> template -> form	 = false;
		$this -> template -> printTemplate();
		
		return true;
	}
}

$LoginLogout	= new \LoginLogout();
