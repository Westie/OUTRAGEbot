<?php
/**
 *	Channel message event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events\Notice;

use \OUTRAGEbot\Event;


class UserNotice extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\User    $user     User which sent the notice
	 *	@supplies string          $message  Message that sent to this user.
	 */
	public function invoke()
	{
		$user = $this->instance->getUser($this->packet->user);
		$message = $this->packet->payload;
		
		return $this->dispatch([ $user, $message ]);
	}
}