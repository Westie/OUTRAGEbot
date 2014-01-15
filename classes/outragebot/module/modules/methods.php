<?php
/**
 *	The Methods module consists of functionality that would have been placed
 *	directly within the bot in previous versions of OUTRAGEbot.
 *
 *	Instead of checking the master class then iterating through all the modules,
 *	everything is an introduced function, therefore helping to speed up
 *	execution of stuff. 
 */


namespace OUTRAGEbot\Module\Modules;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Module;
use \OUTRAGEbot\Connection;


class Methods extends Module\Template
{
	/**
	 *	Called when the module has been loaded into memory.
	 */
	public function construct()
	{
		# automatically add stuff
		$reflection = new \ReflectionObject($this);
		$methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
		
		foreach($methods as $method)
		{
			if($method->getName() == "construct")
				continue;
			
			$this->introduceMethod($method->getName());
		}
	}
	
	
	/**
	 *	Join a channel.
	 *
	 *	@param mixed $channel    Channel name
	 *	@param string $password  Channel password
	 */
	public function join($context, $channel, $password = null)
	{
		$context->instance->raw("JOIN ".$channel.(!empty($password) ? " ".$password : ""));
		return $this;
	}
	
	
	/**
	 *	Leave a channel.
	 *
	 *	@param mixed $channel  Channel name
	 *	@param string $reason  Reason to leave the channel
	 */
	public function part($context, $channel, $reason = null)
	{
		$context->instance->raw("PART ".$channel.(!empty($reason) ? " :".$reason : ""));
		return $this;
	}
	
	
	/**
	 *	Invite a user to a channel.
	 *
	 *	@param mixed $user     User to invite to channel
	 *	@param mixed $channel  Channel to invite this user
	 */
	public function invite($context, $user, $channel)
	{
		$context->instance->raw("INVITE ".$user." ".$channel);
		return $this;
	}
	
	
	/**
	 *	Send a message to the specified channel.
	 *
	 *	@param mixed $channel  Destination channel
	 *	@param mixed $message  Message to be sent to the channel
	 */
	public function message($context, $channel, $message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$context->instance->raw("PRIVMSG ".$channel." :".$item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Send a raw string to the server.
	 *
	 *	@param mixed $message  Message to be sent to the server
	 */
	public function raw($context, $message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$context->instance->raw($item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Send an action to the specified channel.
	 *
	 *	@param mixed $channel  Destination channel
	 *	@param mixed $message  Action to be sent to the channel
	 */
	public function action($context, $channel, $message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$context->instance->raw("PRIVMSG ".$channel." :".chr(1)."ACTION ".$item.chr(1));
		}
		
		return $this;
	}
	
	
	/**
	 *	Returns a user object associated with this context.
	 *
	 *	@param mixed $user  User to be found
	 */
	public function getUser($context, $user)
	{
		return $context->instance->getUser($user);
	}
	
	
	/**
	 *	Returns a channel object associated with this context.
	 *
	 *	@param mixed $channel  Channel to be found
	 */
	public function getChannel($context, $channel)
	{
		return $context->instance->getChannel($channel);
	}
}