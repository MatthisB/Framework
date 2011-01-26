<?php

/**
 *
 *  Author:	Matthis
 *  Date:		01.06.2010
 *
 */

class Template
{
	protected
		$_dir				= '',
		$_template			= '',
		$_vars				= array(),
		$_loops				= array(),
		$_FormFieldClasses	= array(),
		$_content			= '';

	private static
		$_cache				= array(),
		$_constants			= NULL;

	public function __construct($template, $dir = './')
	{
		$this->_dir			= Filter::Folder($dir, true);
		
		if(!preg_match('/'.preg_quote(ROOT, '/').'.*?/i', $this->_dir))
		{
			$this->_dir		= ROOT.$this->_dir;
		}
				
		$this->_template	= preg_replace('/^(.*?)(\.html)?$/', '$1.html', $template);
		$this->_template	= Filter::File($this->_template, true);
	}
	public function __set($var, $value)
	{
		if(is_array($value))
		{
			$this->setLoop($var, $value);
		}
		else
		{
			$this->setVar($var, $value);
		}
	}
	public function setVar($var, $value)
	{
		$this->_vars['{$'.$var.'}']	= $value;
	}
	public function setLoop($var, array $value)
	{
		$this->_loops[$var]	= $value;
	}
	/**
	 * if a form was posted but a necessary field [ $fieldName ] is empty [ $checkVar ] add an (css) errorClass
	 * 
	 * @param	string	$fieldName
	 * @param	mixed	$checkVar
	 * @param	string	$errorClass
	 * @return	void
	 */
	public function FormField_NotEmpty($fieldName, $checkVar = '', $errorClass = 'formError')
	{
		if(\Header\Request::getMethod() == 'POST'
		&& ( !isset($checkVar) || empty($checkVar) ))
		{
			$this->FormField_AddClass($fieldName, $errorClass);
		}
	}
	public function FormField_AddClass($fieldNames, $classes)
	{
		if(!is_array($fieldNames))
		{
			$fieldNames = array($fieldNames);
		}
		foreach($fieldNames as $field)
		{
			$this->_FormFieldClasses[$field] = $classes;
		}
	}
	public function replaceRegex($regex, $replace)
	{
		$this->_content = preg_replace($regex, $replace, $this->_content);
	}
	public function printTemplate()
	{
		$this->runTemplate();
		echo $this->_content;
	}
	public function returnTemplate()
	{
		$this->runTemplate();
		return $this->_content;
	}

	protected function runTemplate()
	{
		try
		{
			$this->loadFile();
			$this->parseFile();
		}
		catch(\Exception\FatalError $error)
		{
			echo $error->getMessage();
		}
	}
	protected function loadFile()
	{
		$path	= $this->_dir.$this->_template;
		$hash	= md5($path);

		if(!array_key_exists($hash, self::$_cache))
		{
			if(!is_readable($path))
			{
				throw new \Exception\FatalError('Template [ <i>'.$path.'</i> ] doesn\'t exist or isn\'t readable!');
			}
			
			if((self::$_cache[$hash] = file_get_contents($this->_dir.$this->_template)) === false)
			{
				throw new \Exception\FatalError('Could not load Template [ <i>'.$path.'</i> ] - unknown reaseon!');
			}
		}

		$this->_content = self::$_cache[$hash];
	}
	protected function parseFile()
	{
		# replace {IF $notEmpty}Wenn $notEmpty gesetzt und nicht leer ist.{ELSE $notEmpty}Wenn $notEmpty leer oder nicht gesetzt ist.{/IF $notEmpty}
		#$this->_content = preg_replace('/\{IF \$([a-zA-Z0-9]+)\}(.*?)\{\/IF \$(\1)\}/ise', '$this->parse_if("$1", "$2")', $this->_content);
		$this->_content = preg_replace_callback('/\{IF \$([a-zA-Z0-9]+)\}(.*?)\{\/IF \$(\1)\}/is', array($this, 'parse_if'), $this->_content);

		# replace {$vars}
		$this->_content = \Helper\String::ReplaceAssoc($this->_vars, $this->_content);

		# replace {_CONSTANT_}
		$this->_content = preg_replace('/\{_([a-zA-Z0-9_]+)_\}/ie', '$this->parse_constant("$1")', $this->_content);

		# replace {* comment *}
		$this->_content = preg_replace('/\{\*[^\*]+\*}/is', '', $this->_content);

		# replace {JS file.js}
		$this->_content = preg_replace('/\{JS ([^}]+)\}/ie', '$this->parse_js("$1")', $this->_content);

		# replace {CSS file.js screen}
		$this->_content = preg_replace('/\{CSS ([^ }]+).?(all|aural|braille|embossed|handheld|print|projection|screen|speech|tty|tv)?\}/ie', '$this->parse_css("$1", "$2")', $this->_content);

		# replace {DATE format timestamp}
		$this->_content = preg_replace('/\{DATE ([^}0-9+]+)( [0-9]+)?\}/ie', '$this->parse_date("$1", "$2")', $this->_content);

		# replace {CAPTCHA width height style}
		$this->_content = preg_replace('/\{CAPTCHA ([a-zA-Z][a-zA-Z0-9_]+\s)?([0-9]+)\s([0-9]+)(\s[a-zA-Z][a-zA-Z0-9]+)?\}/ie', '$this->parse_captcha("$2", "$3", "$1", "$4")', $this->_content);

		# replace {INCLUDE file}
		$this->_content = preg_replace('/\{INCLUDE ([^}]+)\}/ie', '$this->parse_include("$1")', $this->_content);
		
		# replace {PLUGIN pluginname}
		$this->_content = preg_replace('/\{PLUGIN ([a-zA-Z][a-zA-Z0-9_]+)::([a-zA-Z][a-zA-Z0-9_]+)\}/ie', '$this->parse_plugin("$1", "$2")', $this->_content);

		# replace {LOOP $var} Loop-Content {$var[key]} {LOOPELSE $var} Loop-Alternative {/LOOP $var}
		$loopNames = array_keys($this->_loops);
		$loopNames = implode('|', $loopNames);
		if(preg_match_all('/\{LOOP \$('.$loopNames.')\}(.*?)(\{LOOPELSE \$\\1\}(.*?))?\{\/LOOP \$\\1\}/is', $this->_content, $loops, PREG_SET_ORDER) > 0)
		{
			$this->parse_loop($loops);
		}
		
		# insert classes-attributes in selected form-fields
		$this->parse_FormFields();
	}
	protected function parse_FormFields()
	{
		foreach($this->_FormFieldClasses as $field => $classes)
		{
			$field		= preg_quote($field, '/');
			$classes	= (is_array($classes) ? implode(' ', $classes) : $classes);
			$classes	= trim($classes);
			
			if(preg_match('/<input([^>]+)name="'.$field.'"([^>]+)class="([^"]+)"([^>]+)?>/si', $this->_content))
			{
				$this->_content = preg_replace('/<input([^>]+)name="('.$field.')"([^>]+)class="([^"]+)"([^>]+)?\/>/si', '<input$1name="$2"$3class="$4 '.$classes.'"$5 />', $this->_content);
			}
			else
			{
				$this->_content = preg_replace('/<input([^>]+)name="('.$field.')"([^>]+)?\/>/si', '<input$1name="$2"$3 class="'.$classes.'" />', $this->_content);
			}
		}
	}
	protected function parse_constant($constant)
	{
		if(self::$_constants === NULL)
		{
			self::$_constants = array('SITEPATH'					=> \Helper\URL::$_SITEPATH,
								      'CURRENTSITE'					=> \Helper\URL::$_CURRENT,
									  'BENCHMARK_LOADED_CLASSES'	=> \Benchmark::getLoadedClasses(),
									  'BENCHMARK_EXECUTED_QUERIES'	=> \Benchmark::getLoadedQueries(),
									  'BENCHMARK_PARSE_TIME'		=> \Registry::Instance()->Benchmark->getResult('wholeSite'));
		}
		if(!array_key_exists($constant, self::$_constants))
		{
			return '{_'.$constant.'_}';
		}

		return self::$_constants[$constant];
	}
	protected function parse_if($vars)
	{
		$var			= $vars[1];
		$content		= $vars[2];
		$contentFalse	= '';

		if(preg_match('/(.*)\{ELSE \$'.$var.'\}(.*)/is', $content, $matches))
		{
			$content		= $matches[1];
			$contentFalse	= $matches[2];
		}

		if(array_key_exists('{$'.$var.'}', $this->_vars) && !empty($this->_vars['{$'.$var.'}']))
		{
			return $content;
		}
		return $contentFalse;
	}
	protected function parse_loop(array $loops)
	{
		foreach($loops as $loop)
		{
			# TRICKY: Falls Array leer - {LOOPELSE} benutzen - falls nicht gesetzt Stelle mit '' ersetzen
			if(empty($this->_loops[$loop[1]]))
			{
				$this->_content = preg_replace('/\{LOOP \$'.$loop[1].'\}(.*?)\{\/LOOP \$'.$loop[1].'\}/is',
				(!array_key_exists(4, $loop) ? '' : $loop[4]),
				$this->_content);
				continue;
			}
				
			# TRICKY: alle variablen ersetzen
			$replace = '';
			foreach($this->_loops[$loop[1]] as $key => $value)
			{
				$keys	= array_keys($value);
				$values	= array_values($value);

				foreach($keys as $key => $value)
				{
					$keys[$key] = '{$'.$loop[1].'['.$value.']}';
				}

				$replace .= str_replace($keys, $values, $loop[2]);
			}
				
			$this->_content = preg_replace('/\{LOOP \$'.$loop[1].'\}(.*?)\{\/LOOP \$'.$loop[1].'\}/is', $replace, $this->_content);
		}
	}
	protected function parse_css($src, $media)
	{
		$src = \Registry::Instance()->frameworkConfig->sitePath . $src;
		return \Helper\HTML::linkCSS($src, $media);
	}
	protected function parse_js($src)
	{
		$src = \Registry::Instance()->frameworkConfig->sitePath . $src;
		return \Helper\HTML::linkJS($src);
	}
	protected function parse_date($format, $timestamp = '')
	{
		$timestamp = trim($timestamp);
		$timestamp = (empty($timestamp) ? time() : $timestamp);
		return date($format, $timestamp);
	}
	protected function parse_captcha($width, $height, $type = '', $class = '')
	{
		$width	= \Filter::Int($width);
		$height	= \Filter::Int($height);
		$type	= trim($type);
		$class	= trim($class);
		
		$type	= (empty($type) ? 'Math' : $type);
		$type	= '\\Captcha\\'.$type;
		$class	= (empty($class) ? 'default' : $class);
		
		if(!class_exists($type, false) && !is_readable(classFileName($type)))
		{
			trigger_error('Captcha type <i>( '.$type.' )</i> doesn\'t exist!', E_USER_ERROR);
		}
		
		$captcha  = new $type($width, $height);
		$captcha -> createSession();

		$return	= '<div class="Captcha" style="width: '.$width.'px; height: '.$height.'px;">
						<img src="'.\Helper\URL::$_SITEPATH.'captcha/'.$captcha->getID().'/image.png" id="captcha_'.$captcha->getID().'" width="'.$width.'" height="'.$height.'" alt="Captcha" />
						<a href="javascript:reloadCaptcha(\''.$captcha->getID().'\');"><img src="'.\Helper\URL::$_SITEPATH.'templates/arrow_refresh_small.png" width="16" height="16" border="0" alt="Reload" /></a>
						<input type="hidden" name="captchaID" value="'.$captcha->getID().'" />
					</div>';
		
		return $return;		
	}
	protected function parse_include($file)
	{
		$file = \Filter::Folder($file, true);
		if(!is_readable($file))
		{
			return 'Could not read File <i>[ '.$file.' ]</i>.';
		}

		ob_start();
		include($file);
		$return = ob_get_contents();
		ob_end_clean();
			
		return $return;
	}
	protected function parse_plugin($pluginSpace, $pluginName)
	{
		try
		{
			$pluginSpace	= ucfirst($pluginSpace);
			$pluginName		= ucfirst($pluginName);
			$pluginName		= 'Module\\'.$pluginSpace.'\\p_'.$pluginName;
			$pluginFile		= classFileName($pluginName);

			if(!class_exists($pluginName, false) && !is_readable($pluginFile))
			{
				throw new \Exception\Warning('Could not load Plugin <i>[ Name: '.$pluginName.' / File: '.$pluginFile.' ]</i>.');
			}
				
			$pluginObj	= new $pluginName();
			$pluginObj -> runPlugin();
			return $pluginObj -> returnContent();
		}
		catch(\Exception\Warning $warning)
		{
			return $warning->getMessage();
		}
	}
}
