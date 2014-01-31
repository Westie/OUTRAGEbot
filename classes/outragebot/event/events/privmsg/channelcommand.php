<?php
/**
 *	Channel message event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events\Privmsg;

use \OUTRAGEbot\Event;


class ChannelCommand extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 *
	 *	@supplies Element\Channel $channel  Channel in which the command was received
	 *	@supplies Element\User    $user     User which sent the message
	 *	@supplies string          $command  Command name
	 *	@supplies string          $payload	Command payload
	 */
	public function invoke()
	{
		$channel = $this->instance->getChannel($this->packet->parts[2]);
		$user = $this->instance->getUser($this->packet->user);
		$parts = [];
		
		preg_match("/^".preg_quote($this->packet->instance->network->delimiter)."([\S]*)\s?(.*?)?$/", $this->packet->payload, $parts);
		
		return $this->dispatch([ $channel, $user, $parts[1], $parts[2] ]);
	}
}