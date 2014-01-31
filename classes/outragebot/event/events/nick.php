<?php
/**
 *	Handler for the Nick event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Nick extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\User $user      User whose nickname changed - object reflected name change
	 *	@supplies string       $previous  Old nickname of this user.
	 */
	public function invoke()
	{
		$user = $this->instance->getUser($this->packet->user);
		
		$previous = $user->hostmask->nickname;
		$current = $this->packet->payload;
		
		$user->hostmask->nickname = $current;
		$user->hostmask->rebuild();
		
		if(!empty($this->instance->users[$previous]))
			unset($this->instance->users[$previous]);
		
		foreach($this->instance->channels as $channel)
		{
			if(isset($channel->users[$previous]))
			{
				$channel->users[$current] = $channel->users[$previous];
				unset($channel->users[$previous]);
			}
		}
		
		$this->instance->users[$current] = $user;
		
		return $this->dispatch([ $user, $previous ]);
	}
}