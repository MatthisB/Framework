<?php

/**
  *
  *  Author:	Matthis
  *  Date:		26.09.2010
  *
  */

namespace Helper;

class BBCode
{
	protected
		$string				= '',
		$parseTags			= array(),
		$parseSingleTags	= array();
		
	public function __construct($string, $parseTags = array(), $parseSingleTags = array())
	{
		# TODO: Vervollständigen: smileys; 'toggle'
		$this->string	= $string;
		
		if(!is_array($parseTags) || empty($parseTags))
		{
			$this->parseTags	= array('b', 'u', 'i', 's',
										'align', 'p', 'pre', 'color', 'size',
										'img', 'url', 'email',
										'noparse', 'quote', 'toggle', 'php', 'code', 'list', 'table');
		}
		if(!is_array($parseSingleTags) || empty($parseSingleTags))
		{
			$this->parseSingleTags	= array('hr', 'br');
		}
	}
	public function parseBBCode()
	{
		$this->string = $this->parseBBCodeSingleTags($this->string);
		$this->string = $this->parseBBCodeTags($this->string);
		
		return $this->string;
	}

	private function parseBBCodeTags($var)
	{		
		/*
		TODO: old version - if the new one's working great, delete this
		if(is_array($var) && count($var) === 4)
		{			
			$function	= 'parse_'.strtolower($var[1]);
			$var[2]		= ltrim($var[2], ' =');
						
			if(in_array($var[1], $this->parseTags)
			&& \isValid::FunctionName($function)
			&& method_exists(__CLASS__, $function))
			{
				$args	= array('_ORIGINAL_'	=> $var[0],							# complete match
				
								0				=> $var[1],							# tag name
								1				=> $var[3],							# content
								
								2				=> $var[2],							# tag parameters
								3				=> $this->parseParameter($var[2])	# parsed tag parameters
							);
				$var	= $this->{$function}($args);
			}
			else
			{
				$var = '&#91;'.$var[1].$var[2].'&#93'.$var[3].'&#91;/'.$var[1].'&#93';
			}
		}

		$var = preg_replace_callback('/\[(\w+)((?:\s|=)[^]]*)?\]((?:[^[]|\[(?!\/?\1((?:\s|=)[^]]*)?\])|(?R))+)\[\/\1\]/', array($this, 'parseBBCodeTags'), $var);
		*/
		
		if(preg_match_all('/\[(\w+)((?:\s|=)[^]]*)?\]((?:[^[]|\[(?!\/?\1((?:\s|=)[^]]*)?\])|(?R))+)\[\/\1\]/', $var, $matches, PREG_SET_ORDER) > 0)
		{
			foreach($matches as $match)
			{
				$function	= 'parse_'.strtolower($match[1]);
				$match[2]	= ltrim($match[2], ' =');
						
				if(in_array($match[1], $this->parseTags)
				&& \isValid::FunctionName($function)
				&& method_exists(__CLASS__, $function))
				{
					$args	= array('_ORIGINAL_'	=> $match[0],							# complete match
					
									0				=> $match[1],							# tag name
									1				=> $match[3],							# content
									
									2				=> $match[2],							# tag parameters
									3				=> $this->parseParameter($match[2])		# parsed tag parameters
								);
					$replace	= $this->{$function}($args);
				}
				else
				{
					$replace = '&#91;'.$match[1].$match[2].'&#93'.$match[3].'&#91;/'.$match[1].'&#93';
				}
				
				$replace	= $this->parseBBCodeTags($replace);
				$var		= str_replace($match[0], $replace, $var);
			}
		}
		
		return $var;
	}
	private function parseBBCodeSingleTags($var)
	{
		if(is_array($var))
		{
			$function = 'parse_single_'.strtolower($var[1]);

			if(in_array($var[1], $this->parseSingleTags)
			&& \isValid::FunctionName($function)
			&& method_exists(__CLASS__, $function))
			{
				$args	= array($var[1],
								(array_key_exists(2, $var) ? $var[2] : ''),
								(array_key_exists(3, $var) ? $this->parseParameter($var[3]) : array()));
				$var	= $this->{$function}($args);
			}
			else
			{
				$var = $this->replaceBrackets($var[0]);
			}
		}
		
		$var = preg_replace_callback('/\[(\w+)(?:\s"([^"]+)")?((?:\s|=)[^\]]*)?\](?!.*\[\/\1\])/s', array($this, 'parseBBCodeSingleTags'), $var);
		
		return $var;
	}
	private function parseParameter($args)
	{
		$parameterArray = array();
		if(preg_match('/([^\s]+)/', $args, $parameter))
		{
			$parameterArray[0]	= $parameter[1];
		}
		if(preg_match_all('/(\w+)(?:\s?=\s?)(["\'])([^\2]+)\2/', $args, $parameters, PREG_SET_ORDER))
		{
			foreach($parameters as $key => $value)
			{
				$parameterArray[$value[1]] = $value[3];
			}
		}
		return $parameterArray;
	}
	private function replaceBrackets($var)
	{
		 return str_replace(array('[', ']'), array('&#91;', '&#93'), $var);
	}
	private function replaceError($tag, $options, $content)
	{
		$openTag	= '['.$tag.' '.$options.']';
		$closeTag	= '[/'.$tag.']';
		
		return $this->replaceBrackets($openTag).$content.$this->replaceBrackets($closeTag);
	}
	
	private function parse_single_br($args)
	{
		return '<br />';
	}
	private function parse_single_hr($args)
	{
		return '<hr />';
	}
	
	private function parse_noparse($args)
	{
		return $this->replaceBrackets($args['_ORIGINAL_']);
	}
	private function parse_b($args)
	{
		return '<b>'.$args[1].'</b>';
	}
	private function parse_u($args)
	{
		return '<u>'.$args[1].'</u>';
	}
	private function parse_s($args)
	{
		return '<s>'.$args[1].'</s>';
	}
	private function parse_i($args)
	{
		return '<i>'.$args[1].'</i>';
	}
	private function parse_size($args)
	{
		if(!array_key_exists(0, $args[3])
		|| !\isValid::Numeric($args[3][0])
		|| !( $args[3][0] >= 5 && $args[3][0] <= 25 ))
		{
			return $this->replaceError($args[0], $args[2], $args[1]);
		}
		
		return '<p class="BBCode_styling_p" style="font-size: '.$args[3][0].'px;">'.$args[1].'</p>';
	}
	private function parse_align($args)
	{
		$align = (array_key_exists(0, $args[3]) ? strtolower($args[3][0]) : false);
		
		switch($align)
		{
			case 'left':
			case 'center':
			case 'right':
				return '<p class="BBCode_styling_p" style="text-align: '.$align.';">'.$args[1].'</p>';
				
			default:
				return $this->replaceError($args[0], $args[2], $args[1]);
		}
	}
	private function parse_pre($args)
	{
		return '<pre class="BBCode_pre">'.$this->replaceBrackets($args[1]).'</pre>';
	}
	private function parse_color($args)
	{
		if(!array_key_exists(0, $args[3])
		|| !preg_match('/^[\da-f]{6}$/i', $args[3][0]))
		{
			return $this->replaceError($args[0], $args[2], $args[1]);
		}
		
		return '<p class="BBCode_styling_p" style="color: #'.$args[3][0].';">'.$args[1].'</p>';
	}
	private function parse_img($args)
	{
		if(!\isValid::URL($args[1]))
		{
			return $this->replaceError($args[0], $args[2], $args[1]);
		}
		
		$alt = (array_key_exists(0, $args[3]) ? $args[3][0] : '');
		
		return '<img class="BBCode_img" src="'.$args[1].'" alt="'.$alt.'" />';
	}
	private function parse_url($args)
	{
		if(array_key_exists(0, $args[3]) 
		&& \isValid::URL($args[3][0]))
		{
			return '<a class="BBCode_url" href="'.$args[3][0].'">'.$args[1].'</a>';
		}
		elseif(\isValid::URL($args[1]))
		{
			return '<a class="BBCode_url" href="'.$args[1].'">'.$args[1].'</a>';
		}
		else
		{
			return $this->replaceError($args[0], $args[2], $args[1]);
		}
	}
	private function parse_email($args)
	{
		if(array_key_exists(0, $args[3]) 
		&& \isValid::Email($args[3][0]))
		{
			return '<a class="BBCode_email" href="mailto:'.$args[3][0].'">'.$args[1].'</a>';
		}
		elseif(\isValid::Email($args[1]))
		{
			return '<a class="BBCode_email" href="mailto:'.$args[1].'">'.$args[1].'</a>';
		}
		else
		{
			return $this->replaceError($args[0], $args[2], $args[1]);
		}
	}
	private function parse_quote($args)
	{
		$from	= '';
		if(array_key_exists(0, $args[3]))
		{
			$from	= ' from '.$args[3][0];
		}
		
		$return	 = '<div class="BBCode_quote">'
				  .'	<b>Quote'.$from.':</b>'
				  .'	<hr />'
				  .'	<div style="padding: 5px;">'
				  .'		'.$args[1]
				  .'	</div>'
				  .'</div>';
		return $return;
	}
	private function parse_php($args)
	{
		$phpString	 = html_entity_decode($args[1]);
		if(strpos($phpString, '<?') === false)
		{
			$phpString	 = "<?php\r\n".$phpString;
		}
		if(strpos($phpString, '?>') === false)
		{
			$phpString	.= "\r\n?>";
		}

		$phpString	 = highlight_string($phpString, true);
		
		$phpString	 = preg_replace('/([a-zA-Z0-9,_]+)<\/span><span style="color: #007700">\(/', '<a href="http://www.php.net/$1" target="_blank">$1</a></span><span style="color: #007700">(', $phpString);
		$phpString	 = preg_replace('/function&nbsp;<\/span><span style="color: #0000BB"><a href="http:\/\/www.php.net\/([a-zA-Z0-9,_]+)" target="_blank">\\1<\/a>/', 'function&nbsp;</span><span style="color: #0000BB">$1', $phpString);
		
		$search		 = array('<span style="color: #0000BB">', '<span style="color: #007700">', '<span style="color: #DD0000">', '<span style="color: #FF8000">');
		$replace	 = array('<span style="color: #44c">',    '<span style="color: #373">',    '<span style="color: #c30">',    '<span style="color: #939399">');
		$phpString	 = str_replace($search, $replace, $phpString);
		
		$return		 = '<div class="BBCode_php">'
					  .'	<b>PHP-Code:</b>'
					  .'	<hr />'
					  .'	<div>'
					  .'		'.$phpString
					  .'	</div>'
					  .'</div>';
		return $return;
	}
	private function parse_code($args)
	{
		$codeString	 = html_entity_decode($args[1]);
		$codeString	 = highlight_string($codeString, true);
    			
		$return		 = '<div class="BBCode_code">'
					  .'	<b>Code:</b>'
					  .'	<hr />'
					  .'	<div>'
					  .'		'.$codeString
					  .'	</div>'
					  .'</div>';
		return $return;
	}
	private function parse_table($args)
	{		
		$args[1]	 = preg_replace('/\[tr\](.*?)\[\/tr\]/si',	'<tr>$1</tr>',				$args[1]);
		$args[1]	 = preg_replace('/\[td\](.*?)\[\/td\]/si',	'<td valign="top">$1</td>',	$args[1]);

		
		$return		 = '<table width="100%" border="0" class="BBCode_table" cellpadding="4" cellspacing="1">'
					 . $args[1]
					 . '</table>';
		
		return $return;
	}
	private function parse_list($args)
	{		
		$args[1]	 = preg_replace("/\[\*\](.*?)\n/si", '<li>$1</li>', $args[1]);
		
		switch(strtolower((array_key_exists(0, $args[3]) ? $args[3][0] : '')))
		{
			case '1':
				return '<ol>'.$args[1].'</ol>';
				
			case 'a':
				return '<ol type="A">'.$args[1].'</ol>';
				
			default:
				return '<ul>'.$args[1].'</ul>';
		}
	}
	private function parse_toggle($args)
	{
		$title	 = (array_key_exists(0, $args[3]) ? ': '.$args[3][0] : '');
		$id		 = \Filter::systemID($title).'_'.\Helper\String::random(\Helper\String::ALNUM, 8);
		$id		 = trim($id, '_');
		
		$return	 = '<a href="javascript:Toggle(\''.$id.'\')" class="BBCode_toggle_link asc" id="'.$id.'_link">Toggle'.$title.'</a>'
				 . '<div style="display: none;" class="BBCode_toggle_content" id="'.$id.'_content">'
				 . $args[1]
				 . '</div>';
				 
		return $return;
	}
}
