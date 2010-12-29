<?php
/**
 *	OUTRAGEbot development
 */


define("ROOT", __DIR__);


include "System/Core/Core.php";
include "System/Core/Definitions.php";


Core::initClass();

Core::Library("Channel");
Core::Library("Configuration");
Core::Library("Format");
Core::Library("Handler");
Core::Library("Master");
Core::Library("Plugins");
Core::Library("Socket");
Core::LModule("Timer");
Core::Library("Utilities");


Core::scanConfig();


while(true)
{
	Core::Tick();
	Core::Socket();
	
	usleep(2600);
}