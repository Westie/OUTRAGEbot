<?php
/**
 *	Event template class for OUTRAG3bot
 */


namespace OUTRAGEbot\Event;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;
use \OUTRAGEbot\Connection;
use \OUTRAGEbot\Module;


abstract class Template
{
	/**
	 *	Allows the use of the delegator.
	 */
	use Attributes\Delegator;
	
	
	/**
	 *	Stores the bot instance that the event came from.
	 */
	public $instance = null;
	
	
	/**
	 *	Stores which socket the event came from.
	 */
	public $socket = null;
	
	
	/**
	 *	Stores the packet which the event is based on.
	 */
	public $packet = null;
	
	
	/**
	 *	Called when the event has been constructed.
	 */
	public function __construct(Connection\Instance $instance, Connection\Socket $socket = null, Connection\Packet $packet = null)
	{
		$this->instance = $instance;
		$this->socket = $socket;
		$this->packet = $packet;
	}
	
	
	/**
	 *	Retrieves the short name of this class.
	 *
	 *	If you wish to have a different event name from the name of this class,
	 *	override this as a variable. Therefore, in your class, have something
	 *	like:
	 *
	 *		public $qualified_name = "ChannelTopic";
	 *
	 *	It's suggested that the new qualified name is in sentence case, but you know,
	 *	if it isn't committed to the repo then I'm not going to go after you!
	 *
	 *	@todo: Determine if there is a quicker and more memory efficient way of
	 *	       chieving this. This functionality has not been profiled as of yet.
	 */
	public function getter_qualified_name()
	{
		return $this->qualified_name = (new \ReflectionClass($this))->getShortName();
	}
	
	
	/**
	 *	Retrieves the command or numeric that was used to call this event.
	 */
	public function getter_numeric()
	{
		return $this->numeric = strtolower($this->packet->numeric);
	}
	
	
	/**
	 *	Retrieves the name of this event.
	 */
	public function getter_event_name()
	{
		$event = strtolower($this->qualified_name);
		
		$matches = [];
		
		if(preg_match("/numeric([0-9]{3})/", $event, $matches))
			$event = $matches[1];
		
		return $this->event_name = $event;
	}
	
	
	/**
	 *	Retrieves the event callback for a script to use.
	 */
	public function getter_event_callback()
	{
		return $this->event_callback = "on".$this->qualified_name;
	}
	
	
	/**
	 *	Called to handle event actions.
	 */
	public function invoke()
	{
		return $this->dispatch([ $this ]);
	}
	
	
	/**
	 *	A nice little method that actually deals with sending off the event actions.
	 */
	public function dispatch($args)
	{
		if(count($this->instance->events[$this->numeric]))
		{
			$params = [ $this ];
			
			foreach($this->instance->events[$this->numeric] as $item)
			{
				if($item->invoke($this, $params) === true)
					return $this;
			}
		}
		
		foreach([ $this->event_name, "*" ] as $event)
		{
			if(!count($this->instance->events[$event]))
				continue;
			
			foreach($this->instance->events[$event] as $item)
			{
				if($item->invoke($this, $args) === true)
					return $this;
			}
		}
		
		if($this->instance->scripts)
		{
			foreach($this->instance->scripts as $item)
			{
				if($item->reflection->hasMethod($this->event_callback))
				{
					if($item->reflection->getMethod($this->event_callback)->invokeArgs($item, $args) === true)
						return $this;
				}
			}
		}
		
		return $this;
	}
}