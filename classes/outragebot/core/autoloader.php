<?php
/**
 *	Autoloader register method - we want this to be called
 *	to load all the OUTRAGEbot libraries and classes.
 */


namespace OUTRAGEbot\Core;


class Autoloader
{
	/**
	 *	Stores the folder in which to look for the classes.
	 */
	public $directory = null;
	
	
	/**
	 *	Register the autoloader.
	 */
	public static function register($directory)
	{
		$object = new self();
		$object->directory = $directory;
		
		$reflection = new \ReflectionObject($object);
		
        spl_autoload_register($reflection->getMethod("autoload")->getClosure($object));
	}
	
	
	/**
	 *	Our autoloader method. Not PSR-0 compliant but I don't particularly
	 *	care about this.
	 */
	public function autoload($class)
	{
		$class = str_replace([ "_", "\\" ], DIRECTORY_SEPARATOR, $class);
		$class = strtolower($class);
		
		if(preg_match("/^outragebot\//", $class))
		{
			if(file_exists($this->directory."/classes/".$class.".php"))
			{
				require $this->directory."/classes/".$class.".php";
				return true;
			}
		}
		
		return false;
	}
}