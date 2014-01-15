<?php
/**
 *	Packet class for OUTRAGEbot
 */


namespace OUTRAGEbot\Connection;

use \OUTRAGEbot\Core;


class Packet extends Core\ObjectContainer
{
	/**
	 *	Stores a reference to the instance here.
	 */
	public $instance = null;
	
	
	/**
	 *	Called when the packet has been created.
	 */
	public function __construct(Instance $instance, $input)
	{
		$this->instance = $instance;
		
		$this["raw"] = $input;
		$this["parts"] = explode(" ", $input);
		
		if(substr($this["parts"][0], 0, 1) == ":")
		{
			$this["numeric"] = strtoupper($this["parts"][1]);
			$this["user"] = new Hostmask($instance, substr($this["parts"][0], 1));
		}
		else
		{
			$this["numeric"] = strtoupper($this["parts"][0]);
			$this["user"] = new Hostmask($instance, null);
		}
		
		$this["payload"] = (($position = strpos($input, " :", 2)) !== false) ? substr($input, $position + 2) : '';
	}
	
	
	/**
	 *	And let's convert this back to a nice little string...
	 */
	public function __toString()
	{
		return $this->raw;
	}
}