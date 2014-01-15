<?php
/**
 *	Handler for the join event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Join extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->payload);
		$user = $this->instance->getUser($this->packet->user);
		
		if($user)
		{
			$channel->users[$user->getNickname()] = array
			(
				"object" => $user,
				"modes" => "",
			);
		}
		
		if($this->packet->instance->socket)
			$this->packet->instance->raw("MODE ".$channel);
		
		return $this->dispatch([ $channel, $user ]);
	}
}