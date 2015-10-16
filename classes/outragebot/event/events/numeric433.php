<?php
/**
 *	Handler for the 433 (nickname already in use) numeric event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Event;
use \OUTRAGEbot\Connection;


class Numeric433 extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		if(!$this->socket->prepared)
		{
			if(!empty($this->socket->configuration->alt) && $this->socket->configuration->nick != $this->socket->configuration->alt && $this->packet->parts[3] == $this->socket->configuration->nick)
			{
				$this->socket->setNickname($this->socket->configuration->alt);
				$this->socket->handshake();
			}
			else
			{
				$this->socket->setNickname($this->socket->configuration->nick.rand(1000, 9999));
				$this->socket->handshake();
			}
		}
		
		return parent::invoke();
	}
}