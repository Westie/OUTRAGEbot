<?php
/**
 *	Handler for the join event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Part extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[2]);
		$user = $this->instance->getUser($this->packet->hostmask);
		
		if($user)
			unset($channel->users[$user->getNickname()]);
		
		return $this->dispatch([ $channel, $user ]);
	}
}