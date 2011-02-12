/**
  * 
  *  Author:	Matthis
  *  Date:		02.09.2010
  *
  */

/**
 * a short form for document.getElementById, also for multiple elements
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

/**
 * javascript counterpart to the php function in_array
 */
Array.prototype.contains = function(element)
{
	var length = this.length;
	
	for(var i = 0; i < length; ++i)
	{
		if(this[i] == element)
		{
			return i;
		}
	}
	return false;
}

/**
 * convert mixedVar to int
 * 
 * @param	mixed	mixedVar
 */
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

/**
 * checks if key exists in search-array
 * 
 * @param	mixed	key
 * @param	array	search
 * @return	bool
 */
function array_key_exists(key, search)
{
	if(!search || (search.constructor !== Array && search.constructor !== Object))
	{
		return false;
	}
	
	return key in search;
}

/**
 * checks if i is int
 * 
 * @param	mixed	i
 * @return	bool
 */
function is_int(i)
{
	if(typeof i !== 'number')
	{
		return false;
	}
	
	return !(i % 1);
}

/**
 * ask message ( msg ) if true change page to page, otherwise do nothing
 *
 * @param	string	msg
 * @param	string	page
 */
function DoConfirm(msg, page)
{
	var pop = confirm(msg);
	if(pop == true)
	{
		window.location.href = page;
	}
}

/**
 * change page to site
 * 
 * @param	string	site
 */
function changePage(site)
{
	self.location = site;
}

/**
 * reload the captcha, e.g. if isn't legibly
 * @param	int		id
 */
function reloadCaptcha(id)
{
	var rand	= Math.floor(Math.random()*1000);
	
	document.getElementById('captcha_' + id).src	= FRAMEWORK_CONFIG.SITEPATH + 'captcha/'+id+'/image.png';
}

/**
 * call the slider and change link classname
 * 
 * @param	string	argID
 * @param	mixed	argOptions
 */
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

