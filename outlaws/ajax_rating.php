<?php

/**
  *
  *  Author:	Matthis
  *  Date:		08.09.2010
  *
  */

try
{
	if(isEmpty('rate', 1) || $_POST['rate'] != 'true')
	{
		throw new \Exception\FormError('Wrong Request-Method!');
	}
	if(isEmpty('type', 1) || !\isValid::systemID($_POST['type']))
	{
		throw new \Exception\FormError('No or wrong TYPE given!');
	}
	if(isEmpty('typeID', 1) || !\isValid::systemID($_POST['typeID']))
	{
		throw new \Exception\FormError('No or wrong TYPE-ID given!');
	}
	if(isEmpty('stars', 1) || !\isValid::Numeric($_POST['stars']))
	{
		throw new \Exception\FormError('No or wrong number of stars given!');
	}
	if(isEmpty('value', 1) || !\isValid::Numeric($_POST['value']))
	{
		throw new \Exception\FormError('No or wrong value given!');
	}

	$rating  = new StarRating($_POST['type'], $_POST['typeID'], $_POST['stars']);
	
	if($rating -> runRate($_POST['value']) !== true)
	{
		throw new \Exception\FormError('Unknown Error!');
	}
	
	$rating -> printRating();
}
catch(\Exception\FormError $error)
{
	echo $error -> getErrorMessage();
}
