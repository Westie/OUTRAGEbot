<?php
/**
 *	Delegator class for OUTRAG3bot
 *
 *	This is used to handle all of the events passed to the bot - what to do with them,
 *	where to put it, stuff like that.
 */


namespace OUTRAGEbot\Event;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;
use \OUTRAGEbot\Connection;
use \OUTRAGEbot\Format;


class Delegator
{
	/**
	 *	Denote that this is a singleton object, and that it needs a reflector.
	 */
	use Attributes\Singleton;
	use Attributes\Delegator;
	use Attributes\Delegations;
	
	
	/**
	 *	Returns a ReflectionClass instance of the correct delegator - either straight
	 *	away or eventually.
	 */
	public function getEvent($packet)
	{
		if($packet instanceof Connection\Packet)
			$numeric = is_numeric($packet->numeric) ? "Numeric".$packet->numeric : $packet->numeric;
		else
			$numeric = is_numeric($packet) ? "Numeric".$packet : (string) $packet;
		
		if(!$this->reflector->hasMethod($numeric))
			return $this->getEventHandler($numeric);
		
		return $this->reflector->getMethod($numeric)->invoke($this, $packet);
	}
	
	
	/**
	 *	Returns an instance of the class, if it exists.
	 */
	protected function getEventHandler($numeric)
	{
		if(class_exists('\OUTRAGEbot\Event\Events\\'.$numeric))
			return new \ReflectionClass('\OUTRAGEbot\Event\Events\\'.$numeric);
		
		return new \ReflectionClass('\OUTRAGEbot\Event\Events\Unhandled');
	}
	
	
	/**
	 *	Called when a PRIVMSG has been recieved.
	 */
	public function privmsg(Connection\Packet $packet = null)
	{
		if(!$packet)
			return false;
		
		$namespace = "Privmsg\\";
		
		if($packet->payload[0] == Format\Modifiers::CTCP)
			return $this->getEventHandler($namespace."CTCPRequest");
		
		if(in_array($packet->parts[2][0], $packet->instance->serverconf->chantypes))
		{
			if(substr($packet->payload, 0, strlen($packet->instance->network->delimiter)) == $packet->instance->network->delimiter)
				return $this->getEventHandler($namespace."ChannelCommand");
			
			return $this->getEventHandler($namespace."ChannelMessage");
		}
		
		return $this->getEventHandler($namespace."UserMessage");
	}
	
	
	/**
	 *	Called when a NOTICE has been recieved.
	 */
	public function notice(Connection\Packet $packet = null)
	{
		if(!$packet)
			return false;
		
		$namespace = "Notice\\";
		
		if($packet->payload[0] == Format\Modifiers::CTCP)
			return $this->getEventHandler($namespace."CTCPResponse");
		
		if(in_array($packet->parts[2][0], $packet->instance->serverconf->chantypes))
			return $this->getEventHandler($namespace."ChannelNotice");
		
		return $this->getEventHandler($namespace."UserNotice");
	}
}