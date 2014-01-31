<?php
/**
 *	User/private message event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events\Privmsg;

use \OUTRAGEbot\Event;


class UserMessage extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\User    $user     User which sent the message
	 *	@supplies string          $message  Message that was sent to the channel
	 */
	public function invoke()
	{
		$user = $this->instance->getUser($this->packet->user);
		$message = $this->packet->payload;
		
		return $this->dispatch([ $user, $message ]);
	}
}