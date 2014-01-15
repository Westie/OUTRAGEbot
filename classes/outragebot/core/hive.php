<?php
/**
 *	Hive class for OUTRAG3bot - we'll have all of our network connections
 *	languishing in here. PHP doesn't like anonymous properties.
 */


namespace OUTRAGEbot\Core;

use \OUTRAGEbot\Container;
use \OUTRAGEbot\Connection;
use \OUTRAGEbot\Module;


class Hive extends ObjectContainer
{
	/**
	 *	We'll need the Singleton functionality here.
	 */
	use Attributes\Singleton;
	
	
	/**
	 *	Define our use of an IPC server in this hive.
	 */
	private $server = null;
	
	
	/**
	 *	Called when the hive has been created.
	 */
	public function __construct()
	{
		/*
			$this->server = new Container\Server($this);
			$this->server->connect();
		*/
	}
	
	
	/**
	 *	Called when the hive has been destructed.
	 */
	public function __destruct()
	{
		/*
			$this->server->disconnect();
		*/
	}
	
	
	/**
	 *	We can use this tick function to trigger events, and to just
	 *	let the bot sleep for however long it wants.
	 */
	public function tick()
	{
		usleep(4000);
		
		# cycle through the hivemind
		foreach($this as $instance)
			$instance->poll();
		
		# nudge the modules
		Module\Stack::getInstance()->trigger("poll");
		
		return true;
	}
}