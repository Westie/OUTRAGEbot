<?php
/**
 *	Handler for the topic event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Topic extends Event\Template
{
	/**
	 *	Override the qualified name.
	 */
	public $qualified_name = "ChannelTopic";
	
	
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[2]);
		$user = $this->instance->getUser($this->packet->user);
		
		return $this->dispatch([ $channel, $user, $this->packet->payload ]);
	}
}