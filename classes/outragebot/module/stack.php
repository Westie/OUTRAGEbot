<?php
/**
 *	An object that contains the list of modules that have been loaded into
 *	the system.
 */


namespace OUTRAGEbot\Module;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;


class Stack extends Core\ObjectContainer
{
	/**
	 *	It's necessary for this class to load the Singleton.
	 */
	use Attributes\Singleton;
	
	
	/**
	 *	List of modules that have a requirement to receive ticks
	 *	or pulses.
	 */
	private $events = [];
	
	
	/**
	 *	Scan the modules and load them.
	 *
	 *	@todo: Can I make this nicer?
	 */
	public function scan()
	{
		# basic module events
		$events = [ "poll", "instanceInit" ];
		
		# load global modules
		$files = [];
		
		$files += glob("classes/outragebot/module/modules/*.php");
		$files += glob("modules/*.php");
		
		foreach($files as $file)
		{
			require $file;
			
			$name = basename($file);
			$name = substr($name, 0, -4);
			
			$reflection = new \ReflectionClass('\OUTRAGEbot\Module\Modules\\'.$name);
			
			if(!$reflection)
				continue;
			
			$object = $reflection->newInstance();
			
			foreach($events as $event)
			{
				if($reflection->hasMethod($event))
					$this->events[$event][] = $reflection->getMethod($event)->getClosure($object);
			}
		}
		
		return true;
	}
	
	
	/**
	 *	Called whenever there is a poll event.
	 */
	public function trigger($event, $arguments = null)
	{
		if(!empty($this->events[$event]))
		{
			if(!empty($arguments))
			{
				foreach($this->events[$event] as $closure)
					call_user_func_array($closure, $arguments);
			}
			else
			{
				foreach($this->events[$event] as $closure)
					$closure();
			}
		}
		
		return true;
	}
}