/**
  * 
  *  Author:	Matthis
  *  Date:		05.09.2010
  *
  */

var CountDown = function(argID, argSeconds, argOptions)
{
	var instances	= [];

	if(typeof argID == 'undefined')
	{
		return false;
	}

	var seconds			= (is_int(argSeconds) ? argSeconds : 3);
	
	instances[argID]	= {
			'seconds'		: seconds,
			'rest'			: seconds,
			'displayObj'	: document.getElementById(argID),
			'options'		: (typeof argOptions != 'undefined' ? argOptions : {})
			};

	var startCountdown = function()
	{
		setTimeout(function(){tickCountdown(argID);}, 1000);
	}
	var tickCountdown = function()
	{
		if(instances[argID].rest <= 0)
		{
			endCountdown();
			return true;
		}

		--instances[argID].rest;
		displayCountdown(argID);
		setTimeout(function(){tickCountdown(argID);}, 1000);
	}
	var displayCountdown = function()
	{		
		if(instances[argID].displayObj !== 'undefined')
		{
			instances[argID].displayObj.innerHTML = instances[argID].rest;
		}
	}
	var endCountdown = function()
	{
		if(typeof instances[argID].options.afterCountdown !== 'undefined')
		{			
			instances[argID].options.afterCountdown();
		}
		
		delete(instances[argID]);
	}

	startCountdown();
}

var AjaxRequest = function(argUrl, argMethod, argSuccessHandler, argParams, argOptions)
{
	argMethod			= argMethod.toUpperCase();
	
	var URL				= argUrl;
	var Method 			= (argMethod == 'GET' || argMethod == 'POST' ? argMethod : 'GET');
	var Params			= (typeof argParams != 'undefined' ? argParams : '');
	var Options			= (typeof argOptions != 'undefined' ? argOptions : {});
	var errorHandler	= (typeof Options.errorHandler != 'undefined' ? Options.errorHandler : alert);
	var successHandler	= argSuccessHandler;
	
	var showLoader = function()
	{
		if(Options.hideLoader == true)
		{
			return false;
		}
		
		if(!document.getElementById('AjaxLoader'))
		{
			var AjaxLoader = document.createElement('div');
			
			AjaxLoader.setAttribute('id',		'AjaxLoader');
			AjaxLoader.setAttribute('class',	'AjaxLoader');

			AjaxLoader.innerHTML	= '&nbsp;';
			
			document.body.appendChild(AjaxLoader);
		}
		
		var AjaxLoader = document.getElementById('AjaxLoader');
		if(AjaxLoader.style.display != 'block')
		{
			AjaxLoader.style.display = 'block';
		}
	}
	var hideLoader = function()
	{
		if(Options.hideLoader == true)
		{
			return false;
		}
		
		var AjaxLoader = document.getElementById('AjaxLoader')
		if(typeof AjaxLoader != 'undefined')
		{
			AjaxLoader.style.display = 'none';
		}
	}
	
	var getXMLHttpRequest = function()
	{
		if(window.XMLHttpRequest)
		{
			return new XMLHttpRequest();
		}

		if(window.ActiveXObject)
		{
			try
			{
				return new ActiveXObject('Msxml2.XMLHTTP');
			}
			catch(exception)
			{
				try
				{
					return new ActiveXObject('Microsoft.XMLHTTP')
				}
				catch(exception) {}
			}
		}

		errorHandler('Your browser doesn\'t support AJAX ...');
		
		return null;
	}
	var sendRequest = function()
	{
		if(typeof URL == 'undefined')
		{
			errorHandler('No URL given!');
			return false;
		}
	
		var xmlHttpRequest = getXMLHttpRequest();
		
		if(!xmlHttpRequest)
		{
			errorHandler('Could not create XMLHttpRequest-Object!');
			return false;
		}
		
		if(Method == 'GET')
		{
			xmlHttpRequest.open(Method, URL + '?' + Params, true);
			xmlHttpRequest.onreadystatechange = responseHandler;
			xmlHttpRequest.send(null);
		}
		else
		{
			xmlHttpRequest.open(Method, URL, true);
			xmlHttpRequest.onreadystatechange = responseHandler;
			xmlHttpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			xmlHttpRequest.send(Params);
		}
		
		function responseHandler()
		{
			if(xmlHttpRequest.readyState < 4)
			{
				showLoader();
				return null;
			}
			if(xmlHttpRequest.status == 200)
			{
				hideLoader();
			}
			
			if(xmlHttpRequest.status != 200 && xmlHttpRequest.status != 304)
			{
				errorHandler('Error code: ' + xmlHttpRequest.status + '. Text: ' + xmlHttpRequest.statusText + '.');
				return null;
			}
			
			if(typeof successHandler == 'undefined')
			{
				errorHandler('No Success-Handler defined!');
				return null;
			}

			successHandler(xmlHttpRequest.responseText, xmlHttpRequest.responseXML);
		}
	}
	
	sendRequest();
}

var StarRating = function(argID)
{
	if(typeof argID == 'undefined')
	{
		return false;
	}
	
	var ratingObj	= argID;
	var starID		= argID.id;

	argID			= starID.split('_');
	
	var rating		= {
			type:	argID[2],
			typeID:	argID[3]
			};
	
	var currentResult	= document.getElementById('Rating_CurrentResult_' + argID[2] + '_' + argID[3]).innerHTML;
	currentResult		= currentResult.split('/');

	rating.stars	= currentResult[1];
	rating.current	= {
			value:	parseInt(argID[4]),
			result:	parseInt(currentResult[0]),
			status:	document.getElementById('Rating_Status_' + argID[2] + '_' + argID[3]).innerHTML
			};	
	
	var sendVote = function()
	{
		var params	= 'rate=true&typea=' + rating.type + '&typeID=' + rating.typeID + '&value=' + rating.current.value + '&stars=' + rating.stars;
		var ajaxReq	= new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/rating/', 'POST', showVoteResult, params);
	}
	var showVoteResult = function(text, xml)
	{
		var RatingWrapper		= document.getElementById('Rating_Wrapper_' + rating.type + '_' + rating.typeID);
		RatingWrapper.className	= 'Rating_Wrapper_Rated';
		RatingWrapper.innerHTML	= text;
	}
	
	var mouseOver = function()
	{
		document.getElementById('Rating_Status_' + rating.type + '_' + rating.typeID).innerHTML = '<b>Vote:</b> ' + ratingObj.alt + ' stars';
		
		for(i = 1; i <= rating.current.value; i++)
		{
			document.getElementById('Rating_Star_' + rating.type + '_' + rating.typeID + '_' + i).src = FRAMEWORK_CONFIG.SITEPATH + 'templates/rating/star_3.png';
		}
	}
	var mouseOut = function()
	{
		document.getElementById('Rating_Status_' + rating.type + '_' + rating.typeID).innerHTML = rating.current.status;
		
		for(i = 1; i <= rating.current.result; i++)
		{
			document.getElementById('Rating_Star_' + rating.type + '_' + rating.typeID + '_' + i).src = FRAMEWORK_CONFIG.SITEPATH + 'templates/rating/star_2.png';
		}

		for(i = rating.current.result + 1; i <= rating.current.value; i++)
		{
			document.getElementById('Rating_Star_' + rating.type + '_' + rating.typeID + '_' + i).src = FRAMEWORK_CONFIG.SITEPATH + 'templates/rating/star_1.png';
		}
	}
	
	mouseOver();
	ratingObj.onmouseout	= mouseOut;
	ratingObj.onclick		= sendVote;
}

var Slider = function(argID, argOptions)
{
	var options			= (typeof argOptions == 'undefined' ? {} : argOptions);
	var slideObj		= (typeof argID == 'object' ? argID : $(argID));

	var endHeight		= '';
	var direction		= '';
	var startTime		= '';
	var tickInterval	= '';
	
	if(typeof options.tickspeed == 'undefined')
	{
		options.tickspeed	= 5;
	}
	if(typeof options.duration == 'undefined')
	{
		options.duration	= 350;
	}
	
	this.down = function()
	{
		if(slideObj.style.display != 'none')
		{
			return;
		}
		
		direction	= 'down';
		startSliding();
	}
	
	this.up = function()
	{
		if(slideObj.style.display == 'none')
		{
			return;
		}
		
		direction	= 'up';
		startSliding();
	}
	
	var startSliding = function()
	{
		startTime	= new Date().getTime();
		
		if(direction == 'down')
		{
			slideObj.style.display	= 'block';
			endHeight				= slideObj.offsetHeight;

			slideObj.style.display	= 'none';

			slideObj.style.height	= '1px';
			slideObj.style.display	= 'block';
		}
		else
		{
			endHeight				= slideObj.offsetHeight;
		}
		
		slideObj.style.overflow = 'hidden';
		
		tickInterval = setInterval(tick, options.tickspeed);
	}
	var tick = function()
	{
		var elapsed	= new Date().getTime() - startTime;
		
		if(elapsed > options.duration)
		{
			end();
			return;
		}
		
		var d = Math.round(elapsed / options.duration * endHeight);
		
		if(direction == 'up')
		{
			d = endHeight - d;
		}

		slideObj.style.height = d + 'px';
	}
	var end = function()
	{		
		clearInterval(tickInterval);
		
		if(direction == 'up')
		{
			slideObj.style.display = 'none';
		}
		
		slideObj.style.height = endHeight + 'px';

		if(typeof options.onComplete != 'undefined')
		{			
			options.onComplete();
		}
	}
}
