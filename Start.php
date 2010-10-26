<?php
/**
 *	Starting file for OUTRAGEbot.
 *	Use this file to start the bot.
 *
 *	@ignore
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-BETA7 (Git commit: 8d70b2e7184bcf9035ad860c1bd4db3086a37daf)
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
include "System/Channel.php";
include "System/User.php";
include "System/StaticLibrary.php";
include "System/Configuration.php";


/* Uncomment this to include debugging functionality */
// include "System/Debug.php";


/* Set up the controlling classes */
Control::$pConfig = new ConfigParser();
Control::$pConfig->parseDirectory();


/* A little put together function to you know, reset stuff */
Timers::Create
(
	function()
	{
		Control::$iDeathCount = 0;
	},
	
	60,
	-1
);


/* Bot loop */
while(true)
{
	Control::DeathScan();
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
