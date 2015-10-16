<?php
/**
 *	Configuration class for OUTRAGEbot - well, we need to be able to access
 *	things somehow, right?
 */


namespace OUTRAGEbot\Core;


class Configuration extends ObjectContainer
{
	/**
	 *	Load configuration files for this environment.
	 *	
	 *	Call me stupid, but this is one way of having a nice configuration file
	 *	without creating my own parser. *grin*
	 *
	 *	@todo: code new parser without need for Services_JSON!
	 */
	public function load($target)
	{
		$this->resetContainer();
		
		if(!file_exists($target))
			throw new \Exception("Problem with the configuration - can't find {$target}.");
		
		$source = file($target);
		
		# hurrah for stupidness!
		foreach($source as $line => $item)
		{
			$source[$line] = trim($item);
			
			if($source[$line] == "")
			{
				unset($source[$line]);
				continue;
			}
			
			$endchar = substr($source[$line], -1, 1);
			
			if(preg_match("/^[^\,\:\{\[]$/", $endchar))
				$source[$line] .= ",";
		}
		
		$handler = new \Services_JSON(\SERVICES_JSON_LOOSE_TYPE);
		$configuration = $handler->decode("{ ".implode(" ", $source)." }");
		
		$this->populateContainerRecursively($configuration);
	}
}