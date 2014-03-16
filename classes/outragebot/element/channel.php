<?php
/**
 *	Channel class for OUTRAG3bot
 */


namespace OUTRAGEbot\Element;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Connection;


class Channel extends Core\ObjectContainer
{
	/**
	 *	Context of the object that started this request off.
	 */
	public $context = null;
	
	
	/**
	 *	Store the instance of the current bot here.
	 */
	private $instance = null;
	
	
	/**
	 *	What channel name is this referring to?
	 */
	private $channel = null;
	
	
	/**
	 *	Store a list of users in this channel.
	 */
	public $users = null;
	
	
	/**
	 *	Stores channel modes
	 */
	public $modes = null;
	
	
	/**
	 *	Called when the channel has been initialised.
	 */
	public function __construct(Connection\Instance $instance, $channel)
	{
		$this->instance = $instance;
		$this->channel = $channel;
		
		$this->modes = new ChannelModes();
		$this->users = new Core\ObjectContainer();
	}
	
	
	/**
	 *	Retrieves the channel name.
	 *
	 *	@param void
	 */
	public function getChannelName()
	{
		return $this->channel;
	}
	
	
	/**
	 *	Called to send a message to a channel.
	 *
	 *	@param string $message Message to send to the channel.
	 */
	public function send($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$this->instance->raw("PRIVMSG ".$this->channel." :".$item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Called to send a message to a channel.
	 *
	 *	@param string $message Message to send to the channel.
	 */
	public function message($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$this->instance->raw("PRIVMSG ".$this->channel." :".$item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Called to send a notice to an entire channel.
	 *
	 *	@param string $message Message to notice to this channel.
	 */
	public function notice($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$this->instance->raw("NOTICE ".$this->channel." :".$item);
		}
		
		return $this;
	}
	
	
	/**
	 *	Send an action to the specified channel.
	 *
	 *	@param mixed $message  Action to be sent to the channel
	 */
	public function action($message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$context->instance->raw("PRIVMSG ".$this->channel." :".chr(1)."ACTION ".$item.chr(1));
		}
		
		return $this;
	}
	
	
	/**
	 *	Return the string representation of this object.
	 *	In this instance, it is the name of the channel.
	 */
	public function __toString()
	{
		return $this->channel;
	}
	
	
	/**
	 *	Checks if a user is in this channel.
	 *
	 *	@param Element\User $user  User to search for in channel.
	 */
	public function isUserInChannel($user)
	{
		$nickname = $this->instance->getUser($user)->getNickname();
		
		return isset($this->users[$nickname]);
	}
	
	
	/**
	 *	Checks if this user has voice rights (+) in this channel.
	 *	Might not be supported on all networks.
	 *
	 *	@param Element\User $user  User to check for that mode/right in this channel.
	 */
	public function isUserVoice($user)
	{
		$nickname = $this->instance->getUser($user)->getNickname();
		
		if(!isset($this->users[$nickname]))
			return false;
		
		return preg_match("/[qaohv]/", $this->users[$nickname]) == true;
	}
	
	
	/**
	 *	Checks if this user has half op rights (%) in this channel.
	 *	Might not be supported on all networks.
	 *
	 *	@param Element\User $user  User to check for that mode/right in this channel.
	 */
	public function isUserHalfOp($user)
	{
		$nickname = $this->instance->getUser($user)->getNickname();
		
		if(!isset($this->users[$nickname]))
			return false;
		
		return preg_match("/[qaoh]/", $this->users[$nickname]) == true;
	}
	
	
	/**
	 *	Checks if this user has channel op rights (@) in this channel.
	 *	Might not be supported on all networks.
	 *
	 *	@param Element\User $user  User to check for that mode/right in this channel.
	 */
	public function isUserOp($user)
	{
		$nickname = $this->instance->getUser($user)->getNickname();
		
		if(!isset($this->users[$nickname]))
			return false;
		
		return preg_match("/[qao]/", $this->users[$nickname]) == true;
	}
	
	
	/**
	 *	Checks if this user has admin rights (&) in this channel.
	 *	Might not be supported on all networks.
	 *
	 *	@param Element\User $user  User to check for that mode/right in this channel.
	 */
	public function isUserAdmin($user)
	{
		$nickname = $this->instance->getUser($user)->getNickname();
		
		if(!isset($this->users[$nickname]))
			return false;
		
		return preg_match("/[qa]/", $this->users[$nickname]) == true;
	}
	
	
	/**
	 *	Checks if this user has owner rights (~) in this channel.
	 *	Might not be supported on all networks.
	 *
	 *	@param Element\User $user  User to check for that mode/right in this channel.
	 */
	public function isUserOwner($user)
	{
		$nickname = $this->instance->getUser($user)->getNickname();
		
		if(!isset($this->users[$nickname]))
			return false;
		
		return preg_match("/[q]/", $this->users[$nickname]) == true;
	}
}