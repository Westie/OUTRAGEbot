<?php

/**
 *	IPC container server for OUTRAG3bot.
 *
 *	@todo: Get this working. I want this to work 100% of the time! :)
 */

namespace OUTRAGEbot\Element;

use OUTRAGEbot\Core\ObjectContainer;

class Structure extends ObjectContainer
{
    /**
     *	Context of the object that started this request off.
     */
    public $context = null;
}
