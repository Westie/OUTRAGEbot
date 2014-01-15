<?php
/**
 *	CTCP request event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events\Privmsg;

use \OUTRAGEbot\Event;


class CTCPRequest extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$user = $this->instance->getUser($this->packet->user);
		$payload = substr($this->packet->payload, 1, -1);
		
		return $this->dispatch([ $user, $payload ]);
	}
}