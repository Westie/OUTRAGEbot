<?php
/**
 *	User class for OUTRAG3bot
 */


namespace OUTRAGEbot\Element;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Connection;


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
	 *	Called when the user has been constructed. You can either use
	 *	a nickname or a full hostmask - it will be resolved to a
	 *	Hostmask object anyway.
	 */
	public function __construct($instance, $hostmask)
	{
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
	 *	Retrieves this user's name.
	 */
	public function getNickname()
	{
		return $this->hostmask->nickname;
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
}