<?php
/**
 *	Channel message event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events\Notice;

use \OUTRAGEbot\Event;


class ChannelNotice extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\Channel $channel  Channel in which the notice was sent
	 *	@supplies Element\User    $user     User which sent the notice
	 *	@supplies string          $message  Message
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[2]);
		$user = $this->instance->getUser($this->packet->user);
		$message = $this->packet->payload;
		
		return $this->dispatch([ $channel, $user, $message ]);
	}
}