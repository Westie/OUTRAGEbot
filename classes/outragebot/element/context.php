<?php
/**
 *	This context object is a simple way of ensuring that the correct information
 *	is passed to modules.
 *
 *	Due to how the system's been built - there are no tracking of modules, just
 *	tracking of methods or events that methods export, we can't easily assign a
 *	variable to the module - so we pass this object as the first argument in the
 *	module method definition.
 *
 *	And to think I wanted to get away from doing stuff like this! Oh well, modules
 *	aren't to be tinkered about with unless you know what you're doing anyway...
 */


namespace OUTRAGEbot\Element;


class Context
{
	/**
	 *	The callee property holds whatever the main object that this context refers to.
	 *	In most cases, this will be a script or something.
	 */
	public $callee = null;
	
	
	/**
	 *	The instance property holds the current 'instance' of the bot. Each network has
	 *	its own instance, and all of the sockets are controlled by each of the instances.
	 */
	public $instance = null;
}