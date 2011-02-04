/**
  * 
  *  Author:	Matthis
  *  Date:		31.12.2010
  *
  */


AJAX_CHAT_INSTANCES		= [];
UPDATE_TICK_SPEED		= 5 * 1000;		// milliseconds
USERLIST_TICK_SPEED		= 15 * 1000;	// milliseconds

function toggleAllChats()
{
	var span		= $('ajaxChat_changeStatus').childNodes[0];
	var chats		= document.getElementsByClassName('ajaxChat_chatWrapper');
	
	if(span.className == 'ajaxChat_changeStatus_offline')
	{
		span.className	= 'ajaxChat_changeStatus_online';
		span.innerHTML	= 'go online';

		for(var i = 0; i < chats.length; i++)
		{
			var chat	= $(chats[i].id);
			
			if(chat.getElementsByTagName('div')[0].style.display != 'none')
			{
				returnAjaxChatInstance(chats[i].id.replace('chatWindow_', '')).toggleChat();
			}

			chat.style.display	= 'none';
		}
		
		var ajaxReq = new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/chat/setStatus/offline/', 'GET', function(){}, '');
	}
	else
	{
		span.className	= 'ajaxChat_changeStatus_offline';
		span.innerHTML	= 'go offline';

		for(var i = 0; i < chats.length; i++)
		{
			$(chats[i].id).style.display	= 'block';
		}
		
		var ajaxReq = new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/chat/setStatus/online/', 'GET', function(){}, '');
	}
}

// für den schnelleren Zugriff auf die verschiedenen Container
function returnAjaxChatElements(argChatID)
{
	argChatID							= intval(argChatID);
	var chatWrapper						= $('chatWindow_' + argChatID);
	var chatElements					= {};
	
	chatElements.table					= chatWrapper.getElementsByTagName('table')[0];
	chatElements.tr						= {	'titel':	chatElements.table.getElementsByTagName('tr')[0].getElementsByTagName('td')[0],
											'arrow':	chatElements.table.getElementsByTagName('tr')[0].getElementsByTagName('td')[1].getElementsByTagName('img')[0]
											};
	chatElements.chatWindow				= chatWrapper.getElementsByTagName('div')[0];
	chatElements.chatWindow.userList	= chatElements.chatWindow.getElementsByTagName('ul')[0];
	chatElements.chatWindow.chatContent	= chatElements.chatWindow.getElementsByTagName('div')[0];
	chatElements.chatWindow.InputField	= chatElements.chatWindow.getElementsByTagName('input')[0];

	return chatElements;
}

function returnAjaxChatInstance(argChatID)
{
	if(!array_key_exists(argChatID, AJAX_CHAT_INSTANCES))
	{
		AJAX_CHAT_INSTANCES[argChatID] = new ajaxChat(argChatID);
	}
	
	return AJAX_CHAT_INSTANCES[argChatID];
}

var ajaxChat = function(argChatID)
{
	argChatID				= intval(argChatID);
	
	var updateInterval		= null;
	var userListInterval	= null;
	var lastUpdate			= null;
	var chatObjects			= returnAjaxChatElements(argChatID);
	
	var message_BG_color	= 0;
	
	this.toggleChat = function()
	{
		if(chatObjects.tr.arrow.alt == 'Close')
		{
			chatObjects.tr.arrow.alt	= 'Open';
			chatObjects.tr.arrow.src	= FRAMEWORK_CONFIG.SITEPATH + 'templates/chat_images/bullet_arrow_up.png';
			
			var slider	= new Slider(chatObjects.chatWindow);
			slider.up();
			
			closeChat();
		}
		else
		{			
			chatObjects.tr.arrow.alt	= 'Close';
			chatObjects.tr.arrow.src	= FRAMEWORK_CONFIG.SITEPATH + 'templates/chat_images/bullet_arrow_down.png';
			
			// erst laden nachdem der Slider fertig ist, damit alle Einträge korrekt angezeigt wird
			var slider	= new Slider(chatObjects.chatWindow, {onComplete: this.openChat});
			slider.down();
		}
	}
	this.toUser = function(userID)
	{
		if(!is_int(userID))
		{
			return false;
		}
		
		chatObjects.chatWindow.InputField.value	= '/to ' + userID + ' ';
		chatObjects.chatWindow.InputField.focus();
	}
	this.openChat = function()
	{
		updateContent();
		updateUserList();
		
		updateInterval		= setInterval(updateContent, UPDATE_TICK_SPEED);
		userListInterval	= setInterval(updateUserList, USERLIST_TICK_SPEED);
	}
	
	var closeChat = function()
	{	
		if(updateInterval != null)
		{
			window.clearInterval(updateInterval);
		}
		if(userListInterval != null)
		{
			window.clearInterval(userListInterval);
		}

		var ajaxReq = new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/chat/closeChat/'+argChatID+'/', 'GET', function(){}, '');
	}
	var updateContent = function()
	{
		if(arguments.length == 2)
		{
			var xmlResponse			= arguments[1];

			if(xmlResponse == null)
			{
				return;
			}

			var newEntries			= xmlResponse.getElementsByTagName('entry');
			var scrollToBottom		= (parseInt(chatObjects.chatWindow.chatContent.scrollHeight - chatObjects.chatWindow.chatContent.scrollTop) === parseInt(chatObjects.chatWindow.chatContent.clientHeight) ? true : false);
			var updateUserStatus	= [];
			
			
			for(var i = 0; i < newEntries.length; i++)
			{
				var newEntry		= newEntries[i];
				var p				= document.createElement('p');
				var privateMsg		= (newEntry.getAttribute('private') != '0' ? true : false);
				var msgContent		= '';
				var className		= (message_BG_color % 2 ? 'ajaxChat_Message_BG_grey' : 'ajaxChat_Message_BG_white');
								
				for(var x = 0; x < newEntry.childNodes.length; x++)
				{
					msgContent		+= (privateMsg ? '<i>(private)</i> ' : '') + newEntry.childNodes[x].firstChild.nodeValue + '<br />';
				}
				
				p.className			= 'ajaxChat_chatMessage ' + className;
				p.innerHTML			= '<span>' + newEntry.getAttribute('author') 
									+ ' (<span> ' + newEntry.getAttribute('time') + ' </span>):'
									+ '</span>'
									+ msgContent;
				
				chatObjects.chatWindow.chatContent.appendChild(p);
				
				updateUserStatus[i]	= newEntry.getAttribute('authorID');
				
				++message_BG_color;
			}
			
			// immer zum neuesten eintrag scrollen, falls der User nicht nach oben gescrollt hat und es neue Einträge gibt
			if(scrollToBottom && newEntries.length >= 1)
			{
				p.scrollIntoView();
			}
			
			// für die "schnellere aktualisierung" der user aktivitäten, aber nur wenn die user liste nicht sowieso gerade erst geladen wurde
			var userListLength		= (lastUpdate !== null ? chatObjects.chatWindow.userList.getElementsByTagName('li').length : 0);
			for(var i = 0; i < userListLength; i++)
			{
				var user			= chatObjects.chatWindow.userList.getElementsByTagName('li')[i];
				var deleteIndex		= updateUserStatus.contains(user.getAttribute('userID'));
				
				if(deleteIndex !== false
				&& user.getAttribute('active') != '1')
				{
					user.setAttribute('active', '1');
					user.getElementsByTagName('a')[0].setAttribute('class', 'ajaxChat_UserList_activeUser');
					delete(updateUserStatus[deleteIndex]);
				}
			}
			
			lastUpdate	= xmlResponse.getElementsByTagName('lastUpdate')[0].firstChild.nodeValue;
		}
		else
		{
			var ajaxReq	= new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/chat/update/'+argChatID+'/'+lastUpdate+'/', 'GET', updateContent, '');
		}
	}
	var updateUserList = function()
	{
		if(arguments.length == 2)
		{
			chatObjects.chatWindow.userList.innerHTML = arguments[0];
		}
		else
		{
			var ajaxReq = new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/chat/userList/'+argChatID+'/', 'GET', updateUserList, '');
		}
	}
	var sendMessage = function(e)
	{		
		// sollte in allen gängigen Browsern funktionieren; IE, FF etc.
		if(window.event)
		{
			e = window.event;
		}

		// 13 == Enter Taste
		if(e.keyCode == 13)
		{
			var params	= 'message=' + chatObjects.chatWindow.InputField.value;
			var ajaxReq	= new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/chat/insertMessage/'+argChatID+'/', 'POST', sendMessageResult, params);
		}
	}
	var sendMessageResult = function(html, xml)
	{
		if(typeof html == 'undefined' || html == '')
		{
			chatObjects.chatWindow.InputField.value	= '';
			updateContent();
			
			return true;
		}
		alert("Error: " + html + "\nPlease try again!");
	}
	
	
	chatObjects.chatWindow.InputField.onkeypress	= sendMessage;
}
