<?php
/**
 *	Starting file for OUTRAGEbot.
 *	Use this file to start the bot.
 *
 *	@ignore
 *	@author David Weston <westie@typefish.co.uk>
 *	@licence http://www.typefish.co.uk/licences/
 */


/* Key initiation process. */
error_reporting(0);
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
Control::$oConfig = new ConfigParser();
Control::$oConfig->parseDirectory();


/* Bot loop */
while(true)
{	
	Timers::Scan();
	
	foreach(Control::$aBots as $oMaster)
	{
		$oMaster->Loop();
	}
	
	Timers::Scan();
	usleep(CORE_SLEEP);
}


/* And what happens when we want to leave everything behind? */
exit;
