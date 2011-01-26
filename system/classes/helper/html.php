<?php

/**
 *
 *  Author:	Matthis
 *  Date:		02.06.2010
 *
 */

namespace Helper;

class HTML
{
	public static function EncodeEntities($html)
	{
		$noHTML = htmlentities($html, ENT_QUOTES, 'UTF-8', false);
		return $noHTML;
	}

	public static function redirectJS($page, $seconds = 3, $message = 'You will be redirected in %s seconds.<br /><i>If it\'s not working click <a href="%s">here</a>.</i>')
	{
		$seconds	= \Filter::Int($seconds);
		$randomID	= \Helper\String::random(\Helper\String::ALNUM, 5);
		$message	= sprintf($message, '<i id="RedirectCountdown_'.$randomID.'">'.$seconds.'</i>', $page)."\n";
		$html		= '<script type="text/javascript">'."\n"
					. '<!--'."\n"
					. '	new CountDown("RedirectCountdown_'.$randomID.'", '.$seconds.', {afterCountdown: function(){changePage("'.$page.'");}});'."\n"
					. '-->'."\n"
					. '</script>'."\n";
		
		return $message.$html;
	}
	
	public static function createList(array $listArray, $listType = 'ul', $indentLevel = 0)
	{
		$indent    = str_repeat('	', $indentLevel);
		$listHTML  = $indent."<".$listType.">\n";
		foreach($listArray as $key => $value)
		{
			if(is_array($value))
			{
				$listHTML .= self::createList($value, $listType, ++$indentLevel);
			}
			else
			{
				$listHTML .= $indent."	<li>".$value."</li>\n";
			}
		}
		$listHTML .= $indent."</".$listType.">\n";

		return $listHTML;
	}
	public static function linkPrompt($href, $value, $confirmMessage = 'Sure?', array $attributes = array())
	{
		return '<a href="javascript:void(0);" onclick="DoConfirm(\''.$confirmMessage.'\', \''.$href.'\');"'.self::AttributesToString($attributes).'>'.$value.'</a>';
	}
	public static function linkCSS($filename, $media)
	{
		$media = (empty($media) ? '' : ' media="'.$media.'"');
		return '<link href="'.$filename.'" rel="stylesheet" type="text/css"'.$media.' />'."\n";
	}
	public static function linkJS($filename)
	{
		return '<script type="text/javascript" src="'.$filename.'"></script>'."\n";
	}
	public static function linkA($href, $value, array $attributes = array())
	{
		return '<a href="'.$href.'"'.self::AttributesToString($attributes).'>'.$value.'</a>';
	}
	public static function image($src, $width = '', $height = '', $alt = '', $border = '0', array $attributes = array())
	{
		$attributes['src']		= $src;
		$attributes['width']	= (!empty($width)  ? $width  : NULL);
		$attributes['height']	= (!empty($height) ? $height : NULL);
		$attributes['alt']		= $alt;
		$attributes['border']	= $border;
		$attributes				= self::AttributesToString($attributes);

		return '<img'.$attributes.' />';
	}
	public static function spaces($num = 1)
	{
		return str_repeat('&nbsp;', $num);
	}
	public static function br($num = 1, array $attributes = array())
	{
		$attributes = self::AttributesToString($attributes);
		return str_repeat('<br'.$attributes.' />', $num);
	}
	public static function HeadLine($headline, $type = '1', array $attributes = array())
	{
		$attributes = self::AttributesToString($attributes);
		return '<h'.$type.$attributes.'>'.$headline.'</h'.$type.'>';
	}

	public static function AttributesToString(array $attributes = array())
	{
		$attributesString = ' ';
		foreach($attributes as $key => $value)
		{
			if($value == NULL)
			{
				continue;
			}
			$attributesString .= $key.'="'.$value.'" ';
		}
		return $attributesString;
	}
}
