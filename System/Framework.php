<?php
/**
 *	Framework class for OUTRAGEbot
 *
 *	This class allows the usage of OUTRAGEbot as a simple framework, to send
 *	a message from a forum, or as a plugin for another PHP based deamon.
 *
 *	To look at the callbacks that plugins natively recieved, look at the
 *	'debug02' plugin.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0.1
 */

class Framework
{
	public function __construct()
	{
		define("BASE_DIRECTORY", dirname(__file__));
		require "Definitions.php";

		include "Format.php";
		include "Master.php";
		include "Socket.php";
		include "Plugins.php";
		include "Timers.php";
		include "Control.php";
		include "Configuration.php";
		
		Control::$oConfig = new ConfigParser();
		Control::$oConfig->parseDirectory();
	}
}

?>
