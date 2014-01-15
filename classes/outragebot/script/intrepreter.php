<?php
/**
 *	The script intrepreter for OUTRAG3bot - deals with intrepreting
 *	scripts so that scripts can be reloaded multiple times.
 *
 *	Obviously, this particular class might become obselete whenever
 *	I get the multi-process bot working, but for now, this will help.
 */


namespace OUTRAGEbot\Script;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;


class Intrepreter
{
	/**
	 *	Tell the system that we want this to be a singleton.
	 */
	use Attributes\Singleton;
	
	
	/**
	 *	Called to compile a new script.
	 */
	public function compile($instance, $script)
	{
		$script = strtolower($script);
		$contents = null;
		
		foreach([ "scripts/".$script."/script.php", "scripts/".$script."/".$script.".php", "scripts/".$script.".php" ] as $path)
		{
			if(file_exists($path))
			{
				$contents = file_get_contents($path);
				break;
			}
		}
		
		$namespace = preg_quote('OUTRAGEbot\Script');
		$identifier = substr($script, 0, 10).sha1($contents);
		
		if(file_exists("resources/cache/class-".$identifier.".php"))
		{
			if(!class_exists($identifier))
				require "resources/cache/class-".$identifier.".php";
			
			$object = new $identifier($script, $instance);
		}
		else
		{
			if(!$contents || !preg_match("/class[\s]+?".$script."[\s]+?extends[\s]+?".$namespace."[\s]+?{/i", $contents))
				return null;
			
			$contents = preg_replace("/(class[\s]+?)".$script."([\s]+?extends[\s]+?".$namespace."[\s]+?{)/i", "\\1".$identifier."\\2", $contents);
			$contents = preg_replace("/".$namespace."/", "OUTRAGEbot\Script\Instance", $contents);
			
			file_put_contents("resources/cache/class-".$identifier.".php", $contents);
			
			if(!file_exists("resources/cache/class-".$identifier.".php"))
				return null;
			
			require "resources/cache/class-".$identifier.".php";
			
			$object = new $identifier($script, $instance);
		}
		
		return $object;
	}
}