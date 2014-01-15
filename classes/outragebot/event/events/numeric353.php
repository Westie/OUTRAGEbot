<?php
/**
 *	Handler for the 353 numeric event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Numeric353 extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[4]);
		$hosts = explode(" ", $this->packet->payload);
		
		foreach($hosts as $host)
		{
			$hostmask = new Connection\Hostmask($this->instance, $host);
			$user = $this->instance->getUser($hostmask);
			
			if($user)
			{
				$channel->users[$user->getNickname()] = array
				(
					"object" => $user,
					"modes" => implode("", $hostmask->modes),
				);
			}
		}
		
		return parent::invoke();
	}
}