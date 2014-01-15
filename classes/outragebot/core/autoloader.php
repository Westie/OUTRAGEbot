<?php
/**
 *	Autoloader register method - we want this to be called
 *	to load all the OUTRAGEbot libraries and classes.
 */


namespace OUTRAGEbot\Core;


class Autoloader
{
	/**
	 *	Register the autoloader.
	 */
	public static function register()
	{
        spl_autoload_register(array(new self, "autoload"));
	}
	
	
	/**
	 *	Our autoloader method. Not PSR-0 compliant but I don't particularly
	 *	care about this.
	 */
	public static function autoload($class)
	{
		$class = str_replace(array("_", "\\"), DIRECTORY_SEPARATOR, $class);
		$class = strtolower($class);
		
		if(preg_match("/^outragebot\//", $class))
		{
			if(file_exists("classes/{$class}.php"))
			{
				require "classes/{$class}.php";
				return true;
			}
		}
		
		return false;
	}
}