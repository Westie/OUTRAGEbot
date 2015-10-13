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
		$hosts = explode(" ", trim($this->packet->payload));
		
		foreach($hosts as $host)
		{
			$hostmask = new Connection\Hostmask($this->instance, $host);
			$user = $this->instance->getUser($hostmask);
			
			if($user)
			{
				$prefixes = $this->instance->serverconf->prefixes;
				
				$modes = implode("", $hostmask->modes);
				$modes = str_replace(array_values($prefixes), array_keys($prefixes), $modes);
				
				$channel->users[$user->getNickname()] = array
				(
					"object" => $user,
					"modes" => $modes,
				);
			}
		}
		
		return parent::invoke();
	}
}