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
	 *	Override the qualified name.
	 */
	public $qualified_name = "ChannelKick";
	
	
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\Channel $channel  Channel in which the user was kicked
	 *	@supplies Element\User    $admin    Admin who ejected the user
	 *	@supplies Element\User    $kicked   User that was removed from the channel
	 *	@supplies string          $reason   Reason for being kicked from the channel
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