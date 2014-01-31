<?php
/**
 *	Format module for OUTRAG3bot.
 */


namespace OUTRAGEbot\Module\Modules;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Module;


class Format extends Module\Template
{
	/**
	 *	Defining text formatting
	 */
	const BOLD = "\002";
	const CLEAR = "\017";
	const COLOUR = "\003";
	const CTCP = "\001";
	const INVERSE = "\026";
	const TAB = "\011";
	const ITALIC = "\035";
	const UNDERLINE = "\037";
	
	
	/**
	 *	Defining colours
	 */
	const TEXT_WHITE = "\00300";
	const TEXT_BLACK = "\00301";
	const TEXT_DARK_BLUE = "\00302";
	const TEXT_DARK_GREEN = "\00303";
	const TEXT_RED = "\00304";
	const TEXT_BROWN = "\00305";
	const TEXT_PURPLE = "\00306";
	const TEXT_ORANGE = "\00307";
	const TEXT_YELLOW = "\00308";
	const TEXT_GREEN = "\00309";
	const TEXT_TEAL = "\00310";
	const TEXT_LIGHT_BLUE = "\00311";
	const TEXT_BLUE = "\00312";
	const TEXT_PINK = "\00313";
	const TEXT_DARK_GREY = "\00314";
	const TEXT_GREY = "\00315";
	
	
	/**
	 *	Defining backgrounds too!
	 */
	const BACK_WHITE = ',00';
	const BACK_BLACK = ',01';
	const BACK_DARK_BLUE = ',02';
	const BACK_DARK_GREEN = ',03';
	const BACK_RED = ',04';
	const BACK_BROWN = ',05';
	const BACK_PURPLE = ',06';
	const BACK_ORANGE = ',07';
	const BACK_YELLOW = ',08';
	const BACK_GREEN = ',09';
	const BACK_TEAL = ',10';
	const BACK_LIGHT_BLUE = ',11';
	const BACK_BLUE = ',12';
	const BACK_PINK = ',13';
	const BACK_DARK_GREY = ',14';
	const BACK_GREY = ',15';
	
	
	/**
	 *	Stores the cached versions of these constant names,
	 *	such as lower case and stuff.
	 */
	private $cache = [];
	
	
	/**
	 *	Stores cached patterns.
	 */
	private $patterns = [];
	
	
	/**
	 *	Called when the module has been loaded into memory.
	 */
	public function construct()
	{
		# automatically add stuff
		$reflection = new \ReflectionObject($this);
		$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		
		foreach($methods as $method)
		{
			if($method->getName() == "construct")
				continue;
			
			if($method->getDeclaringClass()->getName() == get_class($this))
				$this->introduceMethod($method->getName());
		}
		
		$this->generateFormatCache();
		$this->generatePatternCache();
	}
	
	
	/**
	 *	Strips the text of formatting.
	 *
	 *	@param string $input  String to be removed of only formatting markers.
	 */
	public function stripFormat($context, $input)
	{
		return preg_replace("/[\002\017\001\026\001\037]/", "", $input);
	}
	
	
	/**
	 *	Strips the text of colours.
	 *
	 *	@param string $input  String to be removed of only colour markers.
	 */
	public function stripColour($context, $input)
	{
		return preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", "", $input);
	}
	
	
	/**
	 *	Strips the text of formatting and colours.
	 *
	 *	@param string $input  String to be removed of all formatting and colour markers.
	 */
	public function stripAll($context, $input)
	{
		return preg_replace("/[\002\017\001\026\001\037]/", "", preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", "", $input));
	}
	
	
	/**
	 *	Provides a way to format certain strings with colour and other formatting markers.
	 *
	 *	@param string $input  String to be parsed.
	 */
	public function format($context, $input)
	{
		foreach($this->patterns as $pattern => $callback)
			$input = preg_replace_callback($pattern, $callback, $input);
		
		return $input;
	}
	
	
	/**
	 *	This method is used to generate the format cache.
	 */
	protected function generateFormatCache()
	{
		$this->cache = array
		(
			"formatting" => [],
			"foreground" => [],
			"background" => [],
		);
		
		$constants = (new \ReflectionObject($this))->getConstants();
		
		foreach($constants as $constant => $value)
		{
			$matches = [];
			
			if(preg_match("/^BACK_(.*)$/", $constant, $matches))
			{
				$constant = $matches[1];
				
				$this->cache["background"][strtolower($constant)] = $value;
				$this->cache["background"][strtolower(str_replace("_", "", $constant))] = $value;
			}
			elseif(preg_match("/^TEXT_(.*)$/", $constant, $matches))
			{
				$constant = $matches[1];
				
				$this->cache["foreground"][strtolower($constant)] = $value;
				$this->cache["foreground"][strtolower(str_replace("_", "", $constant))] = $value;
			}
			else
			{
				$this->cache["formatting"][strtolower($constant)] = $value;
				$this->cache["formatting"][strtolower(str_replace("_", "", $constant))] = $value;
			}
		}
		
		return true;
	}
	
	
	/**
	 *	Generates the pattern cache.
	 */
	protected function generatePatternCache()
	{
		$reflection = new \ReflectionObject($this);
		
		$this->patterns = [];
		
		$this->patterns["/\{[biruv]\}/"] = $reflection->getMethod("parseInputSimpleTag")->getClosure($this);
		$this->patterns["/\{c:(.*?)(:(.*?))?\}/"] = $reflection->getMethod("parseInputColourTag")->getClosure($this);
		
		return true;
	}
	
	
	/**
	 *	This helper method parses the simple tags - bolds, italics, that sort of thing.
	 */ 
	protected function parseInputSimpleTag($matches)
	{
		$tag = $matches[0][1];
		
		switch($tag)
		{
			case 'b':
				return self::BOLD;
			
			case 'i':
				return self::ITALIC;
			
			case 'r':
				return self::CLEAR;
			
			case 'u':
				return self::UNDERLINE;
			
			case 'v':
				return self::INVERSE;
		}
		
		return "";
	}
	
	
	/**
	 *	This helper method parses the advanced tags - pretty much at this moment in time only the colour tag.
	 */
	protected function parseInputColourTag($matches)
	{
		$foreground = strtolower($matches[1]);
		
		if(empty($this->cache["foreground"][$foreground]))
			return "";
		
		$colour = $this->cache["foreground"][$foreground];
		
		if(!empty($matches[3]))
		{
			$background = strtolower($matches[3]);
			
			if(!empty($this->cache["background"][$background]))
				$colour .= $this->cache["background"][$background];
		}
		
		return $colour;
	}
}