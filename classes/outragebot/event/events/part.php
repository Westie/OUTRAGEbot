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
	 *	Override the qualified name.
	 */
	public $qualified_name = "ChannelPart";
	
	
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\Channel $channel  Channel that this user just left
	 *	@supplies Element\User    $user     User that left the channel
	 *	@supplies string          $reason   Reason why this user left the channel
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[2]);
		$user = $this->instance->getUser($this->packet->hostmask);
		
		$this->dispatch([ $channel, $user, $this->packet->payload ]);
		
		if($user)
			unset($channel->users[$user->getNickname()]);
		
		return $this;
	}
}