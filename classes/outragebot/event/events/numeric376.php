<?php
/**
 *	Handler for the 376 numeric event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Numeric376 extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		if(!$this->socket->prepared)
			$this->socket->ready();
		
		return parent::invoke();
	}
}