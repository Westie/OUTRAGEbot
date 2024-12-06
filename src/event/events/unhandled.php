<?php
/**
 *	Unhandled event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;


class Unhandled extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		return parent::invoke();
	}
}