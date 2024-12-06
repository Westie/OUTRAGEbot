<?php
/**
 *	IPC container server for OUTRAG3bot.
 *
 *	@todo: Get this working. I want this to work 100% of the time! :)
 */


namespace OUTRAGEbot\Element;

use \OUTRAGEbot\Core;


class Structure extends Core\ObjectContainer
{
	/**
	 *	Context of the object that started this request off.
	 */
	public $context = null;
}