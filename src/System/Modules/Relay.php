<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     6fa977b99b0dae9e08284c0eb7eef0ed021d9ed8
 *	Committed at:   Sun Jan  1 22:50:25 GMT 2012
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class ModuleRelay
{
	/**
	 *	Storing the relay handlers.
	 */
	private
		$aHandlers = array();


	/**
	 *	Called when the module is loaded.
	 */
	public static function initModule()
	{
		Core::introduceFunction("addRelayHandler", "addHandler");
		Core::introduceFunction("pushToRelay", "Push");
	}


	/**
	 *	Create a handler for relaying information to.
	 */
	public static function addHandler($sPipe, $sCallback)
	{

	}
}
