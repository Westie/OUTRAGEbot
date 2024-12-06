<?php
/**
 *	Hostmask class for OUTRAGEbot
 */


namespace OUTRAGEbot\Connection;

use \OUTRAGEbot\Core;


class Hostmask extends Core\ObjectContainer
{
	/**
	 *	Called when the packet has been created.
	 */
	public function __construct(Instance $instance, $input)
	{
		# some of the most common modes - need to find efficient way of grabbing
		# the modes this server supports...
		$modes = preg_quote("~&%@+.");
		
		$pattern = "/([{$modes}]+)?(.*?)!(.*?)@(.*)/";
		$matches = array();
		
		if($input != null && preg_match($pattern, $input, $matches))
		{
			$this["hostmask"] = $input;
			
			$this["modes"] = preg_split("//", $matches[1], -1, PREG_SPLIT_NO_EMPTY);
			$this["nickname"] = $matches[2];
			$this["username"] = $matches[3];
			$this["hostname"] = $matches[4];
		}
		else
		{
			$this["hostmask"] = null;
			
			$this["modes"] = null;
			$this["nickname"] = $input;
			$this["username"] = $input;
			$this["hostname"] = $input;
		}
	}
	
	
	/**
	 *	Renders a hostmask.
	 */
	public function __toString()
	{
		if($this["modes"] == null)
		{
			return $this["nickname"];
		}
		
		return $this["nickname"]."!".$this["username"]."@".$this["hostname"];
	}
	
	
	/**
	 *	Rebuilds the hostmask, based on other things in this
	 *	object.
	 */
	public function rebuild()
	{
		return $this["hostmask"] = $this["nickname"]."!".$this["username"]."@".$this["hostname"];
	}
}