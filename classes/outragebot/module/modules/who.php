<?php
/**
 *	WHO module for OUTRAG3bot.
 */


namespace OUTRAGEbot\Module\Modules;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Module;
use \OUTRAGEbot\Connection;


class Who extends Module\Template
{
	/**
	 *	Store the WHO response for this request.
	 */
	private $response = null;
	
	
	/**
	 *	Called whenever the module has been loaded into memory.
	 */
	public function construct()
	{
		$this->introduceMethod([ "who", "getWho" ], "retrieve");
	}
	
	
	/**
	 *	Hook for the introduced method.
	 */
	public function retrieve($context)
	{
		return null;
	}
}