<?php

/**
  *
  *  Author:	Matthis
  *  Date:		07.09.2010
  *
  */

/*
CREATE TABLE `fw_rating`
(
	`type`		varchar(255)	NOT NULL default '',
	`typeID`	varchar(255)	NOT NULL default '',
	`votes`		int(11)			NOT NULL default 0,
	`result`	int(11)			NOT NULL default 0,
	
	UNIQUE `entry` ( `type`, `typeID` )
);
*/

class StarRating
{
	private
		$type		= '',
		$typeID		= '',
		
		$stars			= 5,
		$rated			= false,
		$multiRating	= NULL,
		$currentlyRated	= false;
		
	public function __construct($type, $typeID, $stars = 5)
	{
		$this->type		= \Filter::systemID($type);
		$this->typeID	= \Filter::systemID($typeID);
		
		$this->stars	= (\isValid::Numeric($stars) ? \Filter::Int($stars) : 5);

		$this->multiRating	= new \Helper\AvoidSpam('1 DAY', $this->type, $this->typeID);
		$this->rated		= $this->multiRating -> CheckHits();
	}
	public function printRating()
	{
		echo $this->generateStars();
	}
	public function returnRating()
	{
		return $this->generateStars();
	}
	public function runRate($value)
	{
		$value	= \Filter::Int($value);
		
		if($this->rated !== false)
		{
			throw new \Exception\FormError('You have already rated!');
		}

		$query  = 'INSERT INTO 
				  	'.PREFIX.'rating
				   	( type, typeID, votes, result )
				   VALUES
				   	( "'.$this->type.'", "'.$this->typeID.'", 1, '.$value.' )
				   ON DUPLICATE KEY UPDATE
				   	votes = ( votes + 1 ),
				   	result = ( result + '.$value.');';
			
		$sql	= new \mySQL\Query();
		$sql   -> Query($query);

		$this -> multiRating -> Insert();
		$this -> rated	= $this -> multiRating -> CheckHits();
		
		$this -> currentlyRated	= true;
		
		return true;
	}		
	
	private function generateStars()
	{
		ob_start();

		$query  = new \mySQL\Select();
		$query -> selectFrom('votes, ROUND(result / votes) as stars', PREFIX.'rating');
		$query -> where('type = "'.$this->type.'" AND typeID = "'.$this->typeID.'"');
		$query -> exeQuery();
		
		$votes	= 0;
		$stars	= 0;
		
		if($query -> NumRows() == 1)
		{
			$result	= $query -> FetchObj();
			$votes	= $result -> votes;
			$stars	= $result -> stars;
		}
		
		if($this->rated !== false)
		{
			if($this -> currentlyRated)
			{
				\Helper\Message::Success('<b>Bewertung:</b> '.$votes.'x');
			}
			else
			{
				echo '<span><b>Bewertung:</b> '.$votes.'x</span>';
			}
			
			echo '<div class="Rating_Wrapper_Rated">';
			
			$attributes	= array();
		}
		else
		{
			echo '<div id="Rating_Wrapper_'.$this->type.'_'.$this->typeID.'" class="Rating_Wrapper">';
			echo '	<p id="Rating_Status_'.$this->type.'_'.$this->typeID.'" class="Rating_Status"><b>Bewertung:</b> '.$votes.'x</p>';
			echo '	<span id="Rating_CurrentResult_'.$this->type.'_'.$this->typeID.'" class="Rating_CurrentResult">'.$stars.'/'.$this->stars.'</span>';
			
			$starID		= 'Rating_Star_'.$this->type.'_'.$this->typeID.'_';
			$attributes	= array('onmouseover'	=> 'new StarRating(this);');
		}		
		
		for($i = 1; $i <= $stars; $i++)
		{
			if($this->rated === false)
			{
				$attributes['id'] = $starID.$i; 
			}
			echo \Helper\HTML::image('http://localhost/eclipse_workspace/Framework/templates/rating/star_2.png', '16', '16', $i, '0', $attributes);
		}
		for($i = ($stars + 1); $i <= $this->stars; $i++)
		{
			if($this->rated === false)
			{
				$attributes['id'] = $starID.$i; 
			}
			echo \Helper\HTML::image('http://localhost/eclipse_workspace/Framework/templates/rating/star_1.png', '16', '16', $i, '0', $attributes);
		}
		
		echo '</div>';
		
		$ratingContent	= ob_get_contents();
		ob_end_clean();
		return $ratingContent;
	}
}
