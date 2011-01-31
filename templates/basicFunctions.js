/**
  * 
  *  Author:	Matthis
  *  Date:		02.09.2010
  *
  */

function $()
{
	var countArguments	= arguments.length;
	var returnElements	= new Array();

	for(var i = 0; i < countArguments; i++)
	{
		var currentElement	= arguments[i];
		
		if(typeof currentElement == 'string')
		{
			currentElement	= document.getElementById(currentElement);
		}
		if(countArguments == 1)
		{
			return currentElement;
		}
		
		returnElements.push(currentElement);
	}
	
	return returnElements;
}

Array.prototype.contains = function(element)
{
	for(var i = 0; i < this.length; ++i)
	{
		if(this[i] == element)
		{
			return i;
		}
	}
	return false;
}

function intval(mixedVar)
{
	var type = typeof(mixedVar);
	
	if(type === 'boolean')
	{
		return (mixedVar) ? 1 : 0;
	}
	if((type === 'number' || type === 'string') && isFinite(mixedVar))
	{
		return Math.floor(mixedVar);
	}
	
	return 0;
}

function array_key_exists(key, search)
{
	if(!search || (search.constructor !== Array && search.constructor !== Object))
	{
		return false;
	}
	
	return key in search;
}

function checkBrowser(name)
{
	if(navigator.userAgent.toLowerCase().indexOf(name.toLowerCase()) > -1 )
	{
		return true;
	}
	
	return false;
}

function is_int(i)
{
	if(typeof i !== 'number')
	{
		return false;
	}
	
	return !(i % 1);
}

function DoConfirm(msg, page)
{
	var pop = confirm(msg);
	if(pop == true)
	{
		window.location.href = page;
	}
}

function changePage(site)
{
	self.location = site;
}

function reloadCaptcha(id)
{
	var rand	= Math.floor(Math.random()*1000);
	
	document.getElementById('captcha_' + id).src	= 'http://localhost/eclipse_workspace/Framework/captcha/'+id+'/image.png';
}

function Toggle(argID, argOptions)
{
	if(typeof argOptions == 'undefined')
	{
		argOptions = {};
	}

	var linkID		= argID + '_link';
	var link		= $(linkID);
	
	var contentID	= argID + '_content';
	var content		= $(contentID);
		
	var slider		= new Slider(contentID, argOptions);
	
	if(content.style.display == 'none')
	{
		link.className = link.className.replace('asc', 'desc');
		slider.down();
	}
	else
	{
		link.className = link.className.replace('desc', 'asc');
		slider.up();
	}
}

