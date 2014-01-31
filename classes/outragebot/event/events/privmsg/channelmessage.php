<?php
/**
 *	Channel message event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events\Privmsg;

use \OUTRAGEbot\Event;


class ChannelMessage extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\Channel $channel  Channel in which the message was received
	 *	@supplies Element\User    $user     User which sent the message
	 *	@supplies string          $message  Message that was sent to the channel
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[2]);
		$user = $this->instance->getUser($this->packet->user);
		$message = $this->packet->payload;
		
		return $this->dispatch([ $channel, $user, $message ]);
	}
}