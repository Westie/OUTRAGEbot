<?php
/**
 *	Handler for the topic event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Quit extends Event\Template
{
	/**
	 *	Override the qualified name.
	 */
	public $qualified_name = "UserQuit";
	
	
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\User $user    User that left the network
	 *	@supplies string       $reason  Reason for leaving the network
	 */
	public function invoke()
	{
		$user = $this->instance->getUser($this->packet->user);
		
		$this->dispatch([ $user, $this->packet->payload ]);
		
		foreach($this->instance->channels as $channel)
			unset($channel->users[$user->hostmask->nickname]);
		
		unset($this->instance->users[$user->hostmask->nickname]);
		return $this;
	}
}