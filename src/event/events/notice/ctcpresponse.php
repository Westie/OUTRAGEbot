<?php
/**
 *	CTCP request event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events\Notice;

use \OUTRAGEbot\Event;


class CTCPResponse extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\User    $user     User which sent the response
	 *	@supplies string          $payload  CTCP response
	 */
	public function invoke()
	{
		$user = $this->instance->getUser($this->packet->user);
		$payload = substr($this->packet->payload, 1, -1);
		
		return $this->dispatch([ $user, $payload ]);
	}
}