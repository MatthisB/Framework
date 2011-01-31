<?php

/**
  *
  *  Author:	Matthis
  *  Date:		31.12.2010
  *
  */

/*
CREATE TABLE `fw_chatrooms`
(
	`ID`			INT(11)			AUTO_INCREMENT,
	`name`			VARCHAR(35)		NOT NULL,

	PRIMARY KEY ( ID )
) ENGINE = InnoDB;
CREATE TABLE `fw_chatentries`
(
	`from_ID`		INT(11)			NOT NULL,
	`to_ID`			INT(11)			default 0,
	`room_ID`		INT(11)			NOT NULL,
	`time`			TIMESTAMP		default	CURRENT_TIMESTAMP,
	`message`		VARCHAR(255)	default '',

	FOREIGN KEY ( room_ID ) REFERENCES fw_chatrooms ( ID ) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;
CREATE TABLE `fw_chatactivities`
(
	`room_ID`		INT(11)			NOT NULL,
	`user_ID`		INT(11)			NOT NULL,
	`lastUpdate`	TIMESTAMP		default '0000-00-00 00:00:00',
	`lastInsert`	TIMESTAMP		default '0000-00-00 00:00:00',
	`firstOpen`		TIMESTAMP		default CURRENT_TIMESTAMP,

	UNIQUE ( room_ID, user_ID),
	FOREIGN KEY ( room_ID ) REFERENCES fw_chatrooms ( ID ) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE = InnoDB;
*/


# User muss eingeloggt sein um den Chat benutzen zu können.
if(!LOGGEDIN)
{
	die('You have to be logged in to use the chat system!');
}

$chatID		 = \Helper\URL::Instance()->_3;
if(!\isValid::Numeric($chatID)
|| !\AjaxChat::doesRoomExists($chatID))
{
	die('Room ID isn\'t valid or doesn\'t exist!');
}

$ajaxChat	 = new \AjaxChat($chatID);
$action		 = \Helper\URL::Instance()->_2;

switch($action)
{
	case 'update':
		$lastUpdate	 = \Helper\URL::Instance()->_4;
		$entries	 = $ajaxChat -> loadEntries($lastUpdate);
		
		\Registry::Instance()->Header->addHeader('Content-Type', 'application/xml');
		echo '<?xml version="1.0" encoding="UTF-8"?>';
		echo "\n<chat>";
		echo "\n<lastUpdate>".time()."</lastUpdate>";
		
		foreach($entries as $entry)
		{
			$subEntries	= '';
			
			foreach($entry[4] as $subEntry)
			{
				$subEntries .= '<subEntry><![CDATA['.$subEntry.']]></subEntry>';
			}
			
			echo "\n".'<entry authorID="'.$entry[0].'" author="'.$entry[1].'" time="'.$entry[2].'" private="'.$entry[3].'">'.$subEntries.'</entry>';
		}
		
		echo "\n</chat>";
		break;
	
	case 'userList':
		$users	 = $ajaxChat -> loadUserList();
		$colorI	 = 0;
		foreach($users as $userID => $user)
		{
			++$colorI;
			echo '<li userID="'.$userID.'" active="'.$user[0].'" class="ajaxChat_UserList_BG_'.($colorI % 2 ? 'white' : 'grey').'"><a href="javascript:returnAjaxChatInstance('.$ajaxChat -> getChatID().').toUser('.$userID.');" class="ajaxChat_UserList_'.($user[0] == 0 ? 'in' : '').'activeUser">'.$user[1].'</a></li>';
		}
		break;
		
	case 'insertMessage':
		$message	 = (isset($_POST['message']) ? $_POST['message'] : '');
		$insert		 = $ajaxChat -> insertMessage($message);
		if($insert !== true)
		{
			echo $insert;
		}	
		break;
		
	case 'closeChat':
		$ajaxChat -> closeChat();
		break;
}

class AjaxChat
{
	protected
		$chatID		= '';

	public function __construct($chatID)
	{
		$this -> chatID		 = \Filter::Int($chatID);
		
		$sql	 = 'INSERT IGNORE INTO
						'.PREFIX.'chatactivities
						( room_ID, user_ID, firstOpen )
					VALUES
						( 
							'.$this -> chatID.', 
							'.\Session\Scope::Instance() -> user -> ID.',
							NOW()
						 );';
		$query	 = new \mySQL\Query();
		$query	-> sqlQuery($sql);
	}

	public function getChatID()
	{
		return $this -> chatID;
	}
	public function loadEntries($timestamp = NULL)
	{
		if(is_numeric($timestamp))
		{
			$where	= 'FROM_UNIXTIME('.$timestamp.')';
		}
		else
		{
			$where	= '( SELECT firstOpen FROM '.PREFIX.'chatactivities WHERE user_ID = '.\Session\Scope::Instance() -> user -> ID.' )';
		}

		$sql	 = 'SELECT
						user.ID as userID,
						user.nickName as nick,
						chat.message as message,
						DATE_FORMAT(chat.time, "%k:%i") as time,
						chat.to_ID,
						private.nickName as to_nick
					FROM
						'.PREFIX.'chatentries as chat
					INNER JOIN
						'.PREFIX.'user as user
					ON
						chat.from_ID	= user.ID
					LEFT JOIN
						'.PREFIX.'user as private
					ON
						chat.to_ID		= private.ID
					WHERE
						chat.room_ID	= '.$this -> chatID.'
					AND
						'.$where.' < chat.time
					AND
						(
							chat.to_ID = 0
							OR
							chat.to_ID = '.\Session\Scope::Instance() -> user -> ID.'
							OR
							chat.from_ID = '.\Session\Scope::Instance() -> user -> ID.'
						)
					ORDER BY
						chat.time ASC';
		
		$query		 = new \mySQL\Query();
		$query		 -> sqlQuery($sql);

		$entries	 = array();
		
		if($query -> NumRows() >= 1)
		{
			$i		 = 0;
			
			while($entry = $query -> FetchObj())
			{
				# wenn private nachricht, den author in "Author - Empfänger" ändern
				if($entry -> to_ID != 0
				&& ($entry -> to_ID == \Session\Scope::Instance() -> user -> ID
					|| $entry -> userID == \Session\Scope::Instance() -> user -> ID
					)
				)
				{
					$entry -> nick = $entry -> nick.' - '.$entry -> to_nick;
				}
												
				# Um Platz zu sparen, Namen/Uhrzeit nicht erneut anzeigen, 
				# falls die Nachricht in der selben Minute geschrieben wurde wie die letzte und den selben Absender hat ...
				if($i != 0
				&& $entries[$i-1][1] == $entry -> nick
				&& substr($entries[$i-1][2], -2) == substr($entry -> time, -2))
				{
					$entries[$i-1][4][] = $entry -> message;
					continue;
				}
				
				# ansonsten neuer Eintrag
				$entries[$i] = array($entry -> userID,
									$entry -> nick,	
									$entry -> time,
									($entry -> to_ID != 0 ? 1 : 0),
									array($entry -> message));
				
				++$i;
			}
		}
		
		$this -> updateActivities('lastUpdate');
		
		return $entries;
	}
	public function loadUserList()
	{
		$this -> deleteExpiredActivities();
		
		$tolerance = '30 SECOND';
		# könnte auch via 'status DESC, user.nickName ASC' sortiert werden
		$sql	 = 'SELECT
						user.ID,
						user.nickName as nick,
						CASE WHEN 
							act.lastInsert + INTERVAL '.$tolerance.' > NOW()
						THEN 1 ELSE 0 END as status
					FROM
						'.PREFIX.'chatactivities as act
					INNER JOIN
						'.PREFIX.'user as user
					ON
						act.user_ID		= user.ID
					WHERE
						act.room_ID		= '.$this -> chatID.'
					ORDER BY
						user.nickName ASC';
		
		$query		 = new \mySQL\Query();
		$query		 -> sqlQuery($sql);

		$users	 = array();
		
		while($user = $query -> FetchObj())
		{
			$users[$user -> ID] = array($user -> status, $user -> nick);
		}

		return $users;
	}
	public function insertMessage($message)
	{
		$message	 = \Filter::XSS_EscapeString($message);
		$message	 = trim($message);
		
		if(empty($message))
		{
			return '';
		}
		
		$to_ID = 0;
		if(preg_match('/\/to\s([0-9]+)\s(.*)/i', $message, $matches))
		{
			$to_ID		= \Filter::Int($matches[1]);
			$message	= $matches[2];
			if(!\Helper\User::doesUserExistsByID($to_ID))
			{
				return 'There is no User with ID = "'.$to_ID.'"';
			}
		}
		
		$query	 = new \mySQL\Insert();
		$query	-> table(PREFIX.'chatentries');
		$query	-> setCols(array('from_ID','to_ID', 'room_ID', 'message'));
		$query	-> setStack(array(\Session\Scope::Instance() -> user -> ID, $to_ID, $this -> chatID, $message));
		$query	-> exeQuery();
		
		$this -> updateActivities('lastInsert');
		
		return true;
	}
	public function closeChat()
	{
		$sql	 = 'DELETE FROM
						'.PREFIX.'chatactivities
					WHERE
						room_ID	= "'.$this -> chatID.'"
						AND
						user_ID	= "'.\Session\Scope::Instance() -> user -> ID.'";';
		$query	 = new \mySQL\Query();
		$query	-> sqlQuery($sql);
	}
	
	public static function doesRoomExists($chatID)
	{
		$chatID	 = \Filter::Int($chatID);
		
		$sql	 = new \mySQL\Select();
		$sql	-> selectFrom('ID', PREFIX.'chatrooms');
		$sql	-> where('ID = '.$chatID);
		$sql	-> exeQuery();
		
		if($sql -> NumRows() === 1)
		{
			return true;
		}
		
		return false;
	}
	
	private function deleteExpiredActivities()
	{
		$tolerance = '1 MINUTE';
		$sql	 = 'DELETE FROM
						'.PREFIX.'chatactivities
					WHERE
						lastUpdate + INTERVAL '.$tolerance.' < NOW();';
		$query	 = new \mySQL\Query();
		$query	-> sqlQuery($sql);
	}
	private function updateActivities($activity)
	{
		if($activity !== 'lastUpdate'
		&& $activity !== 'lastInsert')
		{
			return false;
		}
		
		
		$sql	 = 'UPDATE
						fw_chatactivities
					SET
						'.$activity.' = NOW()
					WHERE
						room_ID	= '.$this -> chatID.'
						AND
						user_ID = '.\Session\Scope::Instance() -> user -> ID.';';
		$query	 = new \mySQL\Query();
		$query	-> sqlQuery($sql);
		
		if($query -> AffectedRows() !== 1)
		{
			return false;
		}
		
		return true;
	}
}
