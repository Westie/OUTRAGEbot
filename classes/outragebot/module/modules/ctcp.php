<?php
/**
 *	CTCP module - even has sample responses!
 */


namespace OUTRAGEbot\Module\Modules;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Module;


class CTCP extends Module\Template
{
	/**
	 *	Called when the module has been loaded into memory.
	 */
	public function construct()
	{
		$this->introduceMethod("replyCTCP", "reply");
		$this->introduceMethod("requestCTCP", "request");
	}
	
	
	/**
	 *	Called whenever there is a new bot instance.
	 */
	public function instanceInit($instance)
	{
		$this->on($instance, "ctcprequest", "onCTCPRequest");
	}
	
	
	/**
	 *	Reply/respond to a CTCP request.
	 *
	 *	@param mixed $destination  Destination object
	 *	@param mixed $message      Response to be sent back
	 */
	public function reply($context, $destination, $message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$context->instance->raw("NOTICE ".$destination." :".chr(1).$item.chr(1));
		}
		
		return $this;
	}
	
	
	/**
	 *	Request from someone or a channel .
	 *
	 *	@param mixed $destination  Destination object
	 *	@param mixed $message      Response to be sent back
	 */
	public function request($context, $destination, $message)
	{
		if(!is_array($message))
			$message = explode("\n", $message);
		
		foreach($message as $item)
		{
			if(strlen($item))
				$context->instance->raw("PRIVMSG ".$destination." :".chr(1).$item.chr(1));
		}
		
		return $this;
	}
	
	
	/**
	 *	This event is called whenever someone requests CTCP information regarding this
	 *	bot.
	 */
	public function onCTCPRequest($context, $user, $payload)
	{
		$command = explode(" ", $payload)[0];
		$command = strtoupper($command);
		
		switch($command)
		{
			case "VERSION":
				$this->reply($context, $user, "VERSION OUTRAGEbot, by David Weston - v3.0-pre-alpha");
				return true;
			break;
		}
		
		return true;
	}
}