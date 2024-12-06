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
	 *
	 *	@supplies Element\User    $user     User which sent the request
	 *	@supplies string          $payload  CTCP payload - this is not separated into command/payload.
	 */
	public function invoke()
	{
		$user = $this->instance->getUser($this->packet->user);
		$payload = substr($this->packet->payload, 1, -1);
		
		return $this->dispatch([ $user, $payload ]);
	}
}