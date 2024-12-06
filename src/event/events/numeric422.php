<?php

/**
 *	Handler for the 422 (no MOTD available) numeric event for OUTRAG3bot
 */

namespace OUTRAGEbot\Event\Events;

use OUTRAGEbot\Event;

class Numeric422 extends Event\Template
{
    /**
     *	Called whenever this event has been invoked.
     */
    public function invoke()
    {
        if (!$this->socket->prepared) {
            $this->socket->ready();
        }

        return parent::invoke();
    }
}
