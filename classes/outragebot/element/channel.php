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
	 *	Return the string representation of this object.
	 *	In this instance, it is the name of the channel.
	 */
	public function __toString()
	{
		return $this->channel;
	}
}