/**
  * 
  *  Author:	Matthis
  *  Date:		31.12.2010
  *
  */


AJAX_CHAT_INSTANCES		= [];
UPDATE_TICK_SPEED		= 5 * 1000;		// milliseconds
USERLIST_TICK_SPEED		= 15 * 1000;	// milliseconds

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
	
	// für den schnelleren Zugriff auf die verschiedenen Container
	var chatWrapper						= $('chatWindow_' + argChatID);
	var chatObjects						= {};
	chatObjects.table					= chatWrapper.getElementsByTagName('table')[0];
	chatObjects.tr						= {	'status':	chatObjects.table.getElementsByTagName('tr')[0].getElementsByTagName('td')[0].getElementsByTagName('img')[0],
											'titel':	chatObjects.table.getElementsByTagName('tr')[0].getElementsByTagName('td')[1],
											'arrow':	chatObjects.table.getElementsByTagName('tr')[0].getElementsByTagName('td')[2].getElementsByTagName('img')[0]
											};
	chatObjects.chatWindow				= chatWrapper.getElementsByTagName('div')[0];
	chatObjects.chatWindow.userList		= chatObjects.chatWindow.getElementsByTagName('ul')[0];
	chatObjects.chatWindow.chatContent	= chatObjects.chatWindow.getElementsByTagName('div')[0];
	chatObjects.chatWindow.InputField	= chatObjects.chatWindow.getElementsByTagName('input')[0];
	
	
	this.toggleChat = function()
	{
		if(chatObjects.tr.arrow.alt == 'Close')
		{
			chatObjects.tr.arrow.alt	= 'Open';
			chatObjects.tr.arrow.src	= 'http://localhost/eclipse_workspace/Framework/templates/chat_images/bullet_arrow_up.png';
			
			var slider	= new Slider(chatObjects.chatWindow);
			slider.up();
			
			closeChat();
		}
		else
		{			
			chatObjects.tr.arrow.alt	= 'Close';
			chatObjects.tr.arrow.src	= 'http://localhost/eclipse_workspace/Framework/templates/chat_images/bullet_arrow_down.png';
			
			// erst laden nachdem der Slider fertig ist, damit alle Einträge korrekt angezeigt wird
			var slider	= new Slider(chatObjects.chatWindow, {onComplete: openChat});
			slider.down();
		}
	}
	
	var openChat = function()
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
	}
	var updateContent = function()
	{
		if(arguments.length == 2)
		{
			var xmlResponse			= arguments[1];
			var newEntries			= xmlResponse.getElementsByTagName('entry');
			var scrollToBottom		= (parseInt(chatObjects.chatWindow.chatContent.scrollHeight - chatObjects.chatWindow.chatContent.scrollTop) === parseInt(chatObjects.chatWindow.chatContent.clientHeight) ? true : false);
			var updateUserStatus	= [];
			
			for(var i = 0; i < newEntries.length; i++)
			{
				var newEntry		= newEntries[i];
				var span			= document.createElement('span');
				
				// TODO: hier mit CSS arbeiten
				span.innerHTML		= '<b>' + newEntry.getAttribute('author') + '</b> ( ' + newEntry.getAttribute('time') + ' ):<br />' + newEntry.firstChild.nodeValue.replace("\n", '<br />') + '<br /><br />';
				chatObjects.chatWindow.chatContent.appendChild(span);
				
				updateUserStatus[i]	= newEntry.getAttribute('authorID');
			}
			
			// immer zum neuesten eintrag scrollen, falls der User nicht nach oben gescrollt hat und es neue Einträge gibt
			if(scrollToBottom && newEntries.length >= 1)
			{
				span.scrollIntoView();
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
					user.setAttribute('class', 'ajaxChat_UserList_activeUser');
					delete(updateUserStatus[deleteIndex]);
				}
			}
			
			lastUpdate	= xmlResponse.getElementsByTagName('lastUpdate')[0].firstChild.nodeValue;
		}
		else
		{
			var ajaxReq	= new AjaxRequest('http://localhost/eclipse_workspace/Framework/ajax/chat/update/'+argChatID+'/'+lastUpdate+'/', 'GET', updateContent, '');
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
			var ajaxReq = new AjaxRequest('http://localhost/eclipse_workspace/Framework/ajax/chat/userList/'+argChatID+'/', 'GET', updateUserList, '');
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
			var ajaxReq	= new AjaxRequest('http://localhost/eclipse_workspace/Framework/ajax/chat/insertMessage/'+argChatID+'/', 'POST', sendMessageResult, params);
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
