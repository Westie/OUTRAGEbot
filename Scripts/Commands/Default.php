<?php
/**
 *	OUTRAGEbot development
 */


class Commands extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$this->addCommandHandler("PRIVMSG", array(__CLASS__, "onMessageInput"));
	}
	
	
	/**
	 *	Called when there's an input.
	 */
	public function onMessageInput($pMessage)
	{
		$this->Message("westie", print_r($pMessage, true));
	}
}