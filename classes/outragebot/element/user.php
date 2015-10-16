<?php
/**
 *	User class for OUTRAG3bot
 */


namespace OUTRAGEbot\Element;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Connection;
use \OUTRAGEbot\Module;


class User extends Core\ObjectContainer
{
	/**
	 *	Context of the object that started this request off.
	 */
	public $context = null;
	
	
	/**
	 *	Store the current instance.
	 */
	private $instance = null;
	
	
	/**
	 *	Internal representation of a hostmask object.
	 */
	public $hostmask = null;
	
	
	/**
	 *	Stores the last known WHOIS request to this user.
	 *	This will be cached for up to a minute, however this cache is able to be cleared.
	 */
	private $whoiscache = [];
	
	
	/**
	 *	Called when the user has been constructed. You can either use
	 *	a nickname or a full hostmask - it will be resolved to a
	 *	Hostmask object anyway.
	 */
	public function __construct($instance, $hostmask)
	{
		$this->context = new Context();
		$this->context->callee = $this;
		$this->context->instance = $instance;
		
		$this->instance = $instance;
		$this->hostmask = $hostmask instanceof Connection\Hostmask ? $hostmask : new Connection\Hostmask($this->instance, $hostmask);
	}
	
	
	/**
	 *	Performs a check to see if this hostmask is currently an admin or not.
	 */
	public function getter_is_admin()
	{
		if(!isset($this->instance->network->owners))
			return false;
		
		if(is_string($this->instance->network->owners))
			return $this->instance->network->owners == $this->hostmask->hostname;
		
		foreach($this->instance->network->owners as $owner)
		{
			if($owner == $this->hostmask->hostname)
				return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Returns the user's username.
	 */
	public function getter_username()
	{
		return $this->hostmask->username;
	}
	
	
	/**
	 *	Returns the user's nickname.
	 */
	public function getter_nickname()
	{
		return $this->hostmask->nickname;
	}
	
	
	/**
	 *	Returns the user's address/hostmask.
	 */
	public function getter_address()
	{
		return $this->hostmask->hostmask;
	}
	
	
	/**
	 *	Returns all the channels that this user is active in, that can be seen
	 *	from the bot.
	 */
	public function getter_channels()
	{
		return $this->getChannels();
	}
	
	
	/**
	 *	Returns their away status, if they have one.
	 */
	public function getter_away()
	{
		$response = $this->getWhois();
		
		if(!$response)
			return false;
		
		return !empty($response->away) ? $response->away : "";
	}
	
	
	/**
	 *	Checks if this user has been flagged as being an IRC helper.
	 */
	public function getter_is_helper()
	{
		$response = $this->getWhois();
		
		if(!$response)
			return false;
		
		return isset($response->helper);
	}
	
	
	/**
	 *	Checks if this user has been flagged as being an IRC operator.
	 */
	public function getter_is_operator()
	{
		$response = $this->getWhois();
		
		if(!$response)
			return false;
		
		return isset($response->ircOp);
	}
	
	
	/**
	 *	Checks if this user is using a secure connection between their client and the
	 *	server.
	 */
	public function getter_is_secure()
	{
		$response = $this->getWhois();
		
		if(!$response)
			return false;
		
		return isset($response->isSecure);
	}
	
	
	/**
	 *	Retrieves the IP address that this user is connecting via.
	 *	Will only usually return anything if the bot is an operator.
	 */
	public function getter_ip_addr()
	{
		$response = $this->getWhois();
		
		if(!$response)
			return false;
		
		return isset($response->ipAddress) ? $response->ipAddress : "";
	}
	
	
	/**
	 *	Returns how many seconds have passed since this user's last activity.
	 */
	public function getter_idle_time()
	{
		$response = $this->getWhois();
		
		if(!$response)
			return false;
		
		if(isset($response->idleTime))
			return $response->idleTime;
		
		return 0;
	}
	
	
	/**
	 *	Returns, in UNIX timestamp format, the date which this user signed in to the server at.
	 */
	public function getter_signon_time()
	{
		$response = $this->getWhois();
		
		if(!$response)
			return false;
		
		if(isset($response->signonTime))
			return $response->signonTime;
		
		return 0;
	}
	
	
	/**
	 *	Is this user a bot of the current instance?
	 */
	public function getter_is_instance()
	{
		foreach($this->instance->sockets() as $socket)
		{
			if($this->nickname == $socket->nickname)
				return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Iterates through all of the channels that are recognised by the bot, and returns the channels
	 *	that this user is in.
	 *
	 *	Use of the getter method is recommended, if only for a semantic viewpoint.
	 */
	public function getChannels()
	{
		$structure = new Structure();
		
		foreach($this->instance->channels as $channel)
		{
			if($channel->isUserInChannel($this))
				$structure->push($channel);
		}
		
		return $structure;
	}
	
	
	/**
	 *	Retrieves this user's nickname.
	 *
	 *	This method only exists as a way to potentially speed up any internal operations, using
	 *	the provided getter is recommended.
	 */
	public function getNickname()
	{
		return $this->hostmask->nickname;
	}
	
	
	/**
	 *	Performs a WHOIS query.
	 */
	public function getWhois($cached = true)
	{
		if(!isset($this->whoiscache))
			$this->whoiscache = [];
		
		$closure = Module\Stack::getInstance()->getClosure("getWhois");
		
		if(!$closure)
			return null;
		
		if($cached && isset($this->whoiscache["expires"]) && $this->whoiscache["expires"] > time())
			return $this->whoiscache["response"];
		
		$this->whoiscache["response"] = $closure($this->context, $this->hostmask->nickname);
		$this->whoiscache["expires"] = time() + 60;
		
		return $this->whoiscache["response"];
	}
	
	
	/**
	 *	Return the string representation of this object.
	 *	In this instance, it is this person's nickname.
	 */
	public function __toString()
	{
		return $this->hostmask->nickname;
	}
	
	
	/**
	 *	Called to send a message to a user.
	 *
	 *	@param string $message Message to send to this user.
	 */
	public function send($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$this->instance->raw("PRIVMSG ".$this->hostmask->nickname." :".$item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Called to send a message to a user.
	 *
	 *	@param string $message Message to send to this user.
	 */
	public function message($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$this->instance->raw("PRIVMSG ".$this->hostmask->nickname." :".$item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Called to send a notice to a user.
	 *
	 *	@param string $message Message to notice to this user.
	 */
	public function notice($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$this->instance->raw("NOTICE ".$this->hostmask->nickname." :".$item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Send an action to the specified user.
	 *
	 *	@param mixed $message  Action to be sent to the user
	 */
	public function action($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$context->instance->raw("PRIVMSG ".$this->hostmask->nickname." :".chr(1)."ACTION ".$item.chr(1));
		}
		
		return $this;
	}
}