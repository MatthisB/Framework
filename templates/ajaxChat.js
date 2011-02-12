/**
  * 
  *  Author:	Matthis
  *  Date:		31.12.2010
  *
  */

// global ajaxChat constants
AJAX_CHAT_INSTANCES		= [];
UPDATE_TICK_SPEED		= 5 * 1000;		// milliseconds
USERLIST_TICK_SPEED		= 15 * 1000;	// milliseconds

/**
 * open or close all chatrooms set user status to online / offline
 */
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

/**
 * to obtain a faster access to the most important chat container
 */
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

/**
 * to create a singleton instance of the ajaxChat-class
 * 
 * @param	int		argChatID
 * @return	obj		ajaxChat
 */
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
	
	/**
	 * decides what happens by clicking on the chat headline ( open or close chat )
	 */
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
			
			// do not load until the slider is ready, to display entries correctly
			var slider	= new Slider(chatObjects.chatWindow, {onComplete: this.openChat});
			slider.down();
		}
	}
	/**
	 * insert /to userID into chat field
	 * 
	 * @param	int		userID
	 */
	this.toUser = function(userID)
	{
		if(!is_int(userID))
		{
			return false;
		}
		
		chatObjects.chatWindow.InputField.value	= '/to ' + userID + ' ';
		chatObjects.chatWindow.InputField.focus();
	}
	/**
	 * sets update intervals
	 */
	this.openChat = function()
	{
		updateContent();
		updateUserList();
		
		updateInterval		= setInterval(updateContent, UPDATE_TICK_SPEED);
		userListInterval	= setInterval(updateUserList, USERLIST_TICK_SPEED);
	}
	
	/**
	 * delete update intervals and delete from activity in this room
	 */
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
	/**
	 * first call the update url, then parse the xml response and insert into chat window
	 */
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
			
			// scroll down to the newest entry if user had not scrolled to another position
			if(scrollToBottom && newEntries.length >= 1)
			{
				p.scrollIntoView();
			}
			
			// for a faster update of user-activity-list, but not if list has loaded a few seconds before
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
	/**
	 * call user update url and then insert the user list into the chatwindow
	 */
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
	/**
	 * called on keypress, if pressed key == enter send message
	 */
	var sendMessage = function(e)
	{		
		// to work in all common browsers; IE, FF etc.
		if(window.event)
		{
			e = window.event;
		}

		// 13 == enter key -> submit entry
		if(e.keyCode == 13)
		{
			var params	= 'message=' + chatObjects.chatWindow.InputField.value;
			var ajaxReq	= new AjaxRequest(FRAMEWORK_CONFIG.SITEPATH + 'ajax/chat/insertMessage/'+argChatID+'/', 'POST', sendMessageResult, params);
		}
	}
	/**
	 * receive result of insert message, if an error occured alert it
	 */
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
	
	
	// to call sendMessage() if user is writing
	chatObjects.chatWindow.InputField.onkeypress	= sendMessage;
}
