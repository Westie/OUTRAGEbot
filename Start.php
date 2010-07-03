<?php
/**
 *	Starting file for OUTRAGEbot.
 *	Use this file to start the bot.
 *
 *	@ignore
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC1 (Git commit: d11a86d7263068cdab8a74af6e9def63b1a1076c)
 */


/* Key initiation process. */
date_default_timezone_set("Europe/London"); // Change this to your time zone.

define("BASE_DIRECTORY", dirname(__file__));
require "System/Definitions.php";


/* Include the system files. */
include "System/Format.php";
include "System/Master.php";
include "System/Socket.php";
include "System/Plugins.php";
include "System/Timers.php";
include "System/Control.php";
include "System/Configuration.php";


/* Set up the controlling classes */
Control::$pConfig = new ConfigParser();
Control::$pConfig->parseDirectory();


/* Bot loop */
while(true)
{	
	Timers::Scan();
	
	foreach(Control::$aBots as $pMaster)
	{
		$pMaster->Loop();
	}
	
	Timers::Scan();
	usleep(CORE_SLEEP);
}


/* And what happens when we want to leave everything behind? */
exit;
