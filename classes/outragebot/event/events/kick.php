<?php
/**
 *	Handler for the kick event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Kick extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->payload);
		$admin = $this->instance->getUser($this->packet->user);
		$kicked = $this->instance->getUser($this->packet->parts[3]);
		
		if($user)
			unset($channel->users[$this->packet->parts[3]]);
		
		return $this->dispatch([ $channel, $admin, $kicked, $this->packet->payload ]);
	}
}