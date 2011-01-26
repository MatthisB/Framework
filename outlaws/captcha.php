<?php

/**
  *
  *  Author:	Matthis
  *  Date:		18.10.2010
  *
  */

\Registry::Instance()->Header->addHeader('Content-Type', 'image/png');

try
{	
	if(\Helper\URL::Instance()->_method == 'NULL')
	{
		throw new \Exception\ImageError('No Captcha-ID given!');
	}
	
	$captchaObj 	 = new \Captcha\Controller(\Helper\URL::Instance()->_method);
	if(!$captchaObj->sessionExists())
	{
		throw new \Exception\ImageError('The given Captcha-ID doesn\'t exist!');
	}
	$captchaObj 	 = $captchaObj->getParameter();
	
	$captchaIni		 = new \iniHandler('captcha', \iniHandler::READ);
	$captchaClass	 = (empty($captchaObj->class) ? 'default' : $captchaObj->class);
	
	if(!isset($captchaIni->$captchaClass))
	{
		trigger_error('Could not find CaptchaClass <i>( '.$captchaClass.' )</i> in captcha.ini!', E_USER_ERROR);
	}
	
	$captchaClass	 = $captchaIni -> $captchaClass;
	$captchaIMG 	 = new \Captcha\Image($captchaObj->value, $captchaObj->width, $captchaObj->height);
	
	# could be more dynamically
	if(isset($captchaClass['Font']) && \isValid::File($captchaClass['Font'], true))
	{
		$captchaIMG	-> setFont($captchaClass['Font']);
	}
	if(isset($captchaClass['FontSize']) && \isValid::Numeric($captchaClass['FontSize']))
	{
		$captchaIMG	-> setFontSize($captchaClass['FontSize']);
	}
	if(isset($captchaClass['Border']) && \isValid::Bool($captchaClass['Border']))
	{
		$captchaIMG	-> setBorder($captchaClass['Border']);
	}
	if(isset($captchaClass['Lines']) && \isValid::Bool($captchaClass['Lines']))
	{
		$captchaIMG	-> setLines($captchaClass['Lines']);
	}
	$captchaIMG 	-> createCaptcha();
}
catch(\Exception\ImageError $error)
{
	$error -> printErrorPicture();
}
