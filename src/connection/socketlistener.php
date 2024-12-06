<?php
/**
 *	Socket listener class for OUTRAG3bot.
 *	Allows snooping on socket communications based on arguments passed to this.
 */


namespace OUTRAGEbot\Connection;


class SocketListener
{
	/**
	 *	Store the parent socket.
	 */
	private $socket = [];
	
	
	/**
	 *	Store which numerics we are to sniff.
	 */
	private $numerics = [];
	
	
	/**
	 *	Called to initiate the socket listener.
	 */
	public function __construct($socket = null, $arguments = [])
	{
		$this->socket = $socket;
		
		foreach([ "retrieve", "success", "error" ] as $event)
		{
			if(!empty($arguments[$event]))
			{
				foreach($arguments[$event] as $numeric => $callback)
				{
					if($callback != null)
						$callback = $this->toClosure($callback);
					
					$this->numerics[$numeric] = array
					(
						"type" => $event,
						"callback" => $callback,
					);
				}
			}
		}
		
		return true;
	}
	
	
	/**
	 *	This nasty little method will act as a synchronous listener, blocking everything else from
	 *	happening until we get what we want.
	 */
	public function run()
	{
		$this->socket->setSocketListener($this);
		
		while(true)
		{
			$packet = $this->socket->read(false);
			
			if(!$packet)
				continue;
			
			$return = null;
			
			if($this->receive($packet, $return))
			{
				$this->socket->clearSocketListener();
				return $return;
			}
		}
		
		return null;
	}
	
	
	/**
	 *	Checks what to do with the numeric received from the server.
	 */
	public function receive($packet, &$return = null)
	{
		if(!isset($this->numerics[$packet->numeric]))
			return false;
		
		$event = $this->numerics[$packet->numeric];
		$callback = $event["callback"];
		
		if($callback)
			$return = $callback($packet);
		
		switch($event["type"])
		{
			case "success":
			case "error":
				return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Turns callback into a closure.
	 */
	private function toClosure($callback)
	{
		if($callback instanceof \Closure)
		{
			return $callback;
		}
		else
		{
			if(is_array($callback))
			{
				$reflection = new \ReflectionObject($callback[0]);
				
				if($reflection->hasMethod($callback[1]))
					return $reflection->getMethod($callback[1])->getClosure($callback[0]);
			}
			else
			{
				return (new \ReflectionFunction($callback))->getClosure();
			}
		}
		
		return null;
	}
}