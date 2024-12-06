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
	 *	Override the qualified name.
	 */
	public $qualified_name = "ChannelJoin";
	
	
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\Channel $channel  Channel that this user joined
	 *	@supplies Element\User    $user     User that joined this channel
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