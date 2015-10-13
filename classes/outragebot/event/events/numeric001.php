<?php
/**
 *	Handler for the 001 numeric event for OUTRAG3bot
 */


namespace OUTRAGEbot\Event\Events;

use \OUTRAGEbot\Connection;
use \OUTRAGEbot\Element;
use \OUTRAGEbot\Event;
use \OUTRAGEbot\Module;


class Numeric001 extends Event\Template
{
	/**
	 *	Called whenever this event has been invoked.
	 */
	public function invoke()
	{
		# in a state of being awkward, there could be an instance where the server isn't RFC
		# compliant and decided not to send us any MOTD stuff - that, for some insane reason
		# is when I decide to join channels. so, after 15 seconds, if we haven't got either
		# the end-of-MOTD or no-MOTD numerics, I shall just make everything work anyway.
		if($closure = Module\Stack::getInstance()->getClosure("setTimeout"))
		{
			$context = new Element\Context();
			$context->callee = $this;
			
			$closure($context, function()
			{
				if(!$this->socket->prepared)
					$this->socket->ready();
			}, 15);
		}
		
		return parent::invoke();
	}
}