<?php

/**
  *
  *  Author:	Matthis
  *  Date:		02.02.2011
  *
  */

namespace Module\Chat;

class p_AjaxChat extends \Plugins\a_Plugin implements \Plugins\i_Plugin
{
	private
		$output		= '',
		$isOnline	= false;
	
	public function __construct()
	{
		if(!LOGGEDIN)
		{
			return;
		}

		$this	 -> isOnline	 = (!isset(\Session\Scope::Instance() -> ajaxChat -> status) || \Session\Scope::Instance() -> ajaxChat -> status == 'online' ? true : false);
	}
	public function runPlugin()
	{
		if(!LOGGEDIN)
		{
			return;
		}
		
		$this -> listRooms();
		$this -> createChatBar();
	}
	public function returnContent()
	{
		return $this -> output;
	}

	private function listRooms()
	{
		$sql		 = 'SELECT
							ID,
							name
						FROM
							'.PREFIX.'chatrooms';
		$query		 = new \mySQL\Query();
		$query		 -> sqlQuery($sql);

		# start with width of #ajaxChat_changeStatus
		$marginRight = 100;
		$isOnline	 = (!$this -> isOnline ? 'display: none;' : '');
		$loadChats	 = array();
		
		while($room = $query -> FetchObj())
		{
			$isChatOpened	 = $this -> isChatOpened($room -> ID);
			
			$this -> output	.= '
				<div class="ajaxChat_chatWrapper" id="chatWindow_'.$room -> ID.'" style="right: '.$marginRight.'px;'.$isOnline.'">
					<table onclick="returnAjaxChatInstance('.$room -> ID.').toggleChat();" class="ajaxChat_Tab" border="0" cellpadding="0" cellspacing="0">
						<tr>
							<td valign="middle">'.$room -> name.'</td>
							<td width="16" valign="middle"><img src="'.\Helper\URL::$_SITEPATH.'templates/chat_images/bullet_arrow_'.($isChatOpened ? 'down' : 'up').'.png" width="16" height="16" border="0" alt="'.($isChatOpened ? 'Close' : 'Open').'" /></td>
						</tr>
					</table>
					<div class="ajaxChat_ChatWindow"'.($isChatOpened ? '' : 'style="display: none;"').'>
						<ul class="ajaxChat_UserList"><li>&nbsp;</li></ul>
						<div class="ajaxChat_ChatContent"></div>
						<input class="ajaxChat_inputField" type="text" />
					</div>
				</div>';
						
			if($isChatOpened)
			{
				$loadChats[]	 = $room -> ID;
			}
			
			# add width of .ajaxChat_chatWrapper
			$marginRight		+= 250;
		}
		
		if(!empty($loadChats))
		{
			$this -> output		.= "\n<script type=\"text/javascript\">\n"
								 . "<!--\n";
			foreach($loadChats as $ID)
			{
				$this -> output	.= "returnAjaxChatInstance(".$ID.").openChat();\n";
			}
			$this -> output		.= "//-->\n"
								 . "</script>\n";
		}
	}
	private function createChatBar()
	{
		if($this -> isOnline)
		{
			$this	 -> output	.= '<a id="ajaxChat_changeStatus" href="javascript:toggleAllChats();"><span class="ajaxChat_changeStatus_offline">go offline</span></a>';
		}
		else
		{
			$this	 -> output	.= '<a id="ajaxChat_changeStatus" href="javascript:toggleAllChats();"><span class="ajaxChat_changeStatus_online">go online</span></a>';
		}
	}
	private function isChatOpened($chatID)
	{
		$chatID		 = \Filter::Int($chatID);
		$sql		 = 'SELECT
							true
						FROM
							'.PREFIX.'chatactivities
						WHERE
							room_ID	= '.$chatID.'
							AND
							user_ID	= '.\Session\Scope::Instance() -> user -> ID.';';
		$query		 = new \mySQL\Query();
		$query		 -> sqlQuery($sql);
		
		return ($query -> NumRows() == 1 ? true : false);
	}
}
