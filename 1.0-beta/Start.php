<?php


/* Key initiation process. */
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set("Europe/London");

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
	foreach(Control::$aBots as $oMaster)
	{
		$oMaster->Loop();
	}
	
	Timers::Scan();
	usleep(CORE_SLEEP);
}

exit;
?>
