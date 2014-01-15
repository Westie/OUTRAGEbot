<?php
/**
 *	WHO module for OUTRAG3bot.
 */


namespace OUTRAGEbot\Module\Modules;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Module;
use \OUTRAGEbot\Connection;


class Resources extends Module\Template
{
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
			
			$this->introduceMethod($method->getName());
		}
	}
	
	
	/**
	 *	Proxies file_exists requests for resources within scripts.
	 *
	 *	@param string $filename  Relative path of file.
	 */
	public function resourceExists($context, $filename)
	{
		$root = $this->basepath($context);
		$target = realpath($root.DIRECTORY_SEPARATOR.$filename);
		
		if(strpos($target, $root) !== 0)
			return false;
		
		return file_exists($target);
	}
	
	
	/**
	 *	Proxies file_put_contents requests for resources within scripts.
	 *
	 *	@param string $filename  Relative path of file.
	 *	@param string $string    Contents of file.
	 */
	public function resourcePut($context, $filename, $string)
	{
		$root = $this->basepath($context);
		$target = $root.DIRECTORY_SEPARATOR.$filename;
		
		if(stristr($target, "../"))
			return false;
		
		if(strpos($target, $root) !== 0)
			return false;
		
		return file_put_contents($target, $string);
	}
	
	
	/**
	 *	Proxies file_get_contents requests for resources within scripts.
	 *
	 *	@param string $filename  Relative path of file.
	 */
	public function resourceGet($context, $filename)
	{
		$root = $this->basepath($context);
		$target = realpath($root.DIRECTORY_SEPARATOR.$filename);
		
		if(strpos($target, $root) !== 0)
			return false;
		
		return file_get_contents($target);
	}
	
	
	/**
	 *	Proxies glob requests for resources within scripts.
	 *
	 *	@param string $filename  Relative path of file.
	 */
	public function resourceScan($context, $pattern)
	{
		$root = $this->basepath($context);
		$length = strlen($root) + 1;
		
		$target = $root.DIRECTORY_SEPARATOR.$pattern;
		
		if(stristr($target, "../"))
			return [];
		
		if(strpos($target, $root) !== 0)
			return [];
		
		$set = glob($target);
		
		foreach($set as $key => $item)
			$set[$key] = substr($item, $length);
		
		return $set;
	}
	
	
	/**
	 *	Proxies unlink requests for resources within scripts.
	 *
	 *	@param string $filename  Relative path of file.
	 */
	public function resourceUnlink($context, $filename)
	{
		$root = $this->basepath($context);
		$target = realpath($root.DIRECTORY_SEPARATOR.$filename);
		
		if(strpos($target, $root) !== 0)
			return false;
		
		return unlink($target);
	}
	
	
	/**
	 *	Retrieves the base path for the specified context.
	 */
	private function basepath($context)
	{
		$root = realpath("resources/scripts/");
		$root .= "/".$context->callee->_real_script_name."/";
		
		if(!is_dir($root))
			mkdir($root);
		
		return $root;
	}
}