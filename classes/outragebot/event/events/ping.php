<?php
/**
 *	Handler for the join event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Ping extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$this->socket->write("PONG ".$this->packet->payload);
		
		return $this->dispatch([ ]);
	}
}