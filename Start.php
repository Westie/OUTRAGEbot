<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     09c68fbaed58f5eaf8f1066c15fd6277f02d8812
 *	Committed at:   Sat Nov 26 19:52:49 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


define("ROOT", __DIR__);


include "System/Core/Core.php";
include "System/Core/Definitions.php";


Core::initClass();

Core::Library("Child");
Core::LModule("Timer");
Core::Library("Channel");
Core::Library("Configuration");
Core::Library("Format");
Core::Library("Handler");
Core::Library("Master");
Core::Library("MessageObject");
Core::Library("Resource");
Core::Library("Script");
Core::Library("Socket");
Core::Library("User");
Core::Library("Utilities");

Core::Module("CTCP");
Core::Module("Who");
Core::Module("Whois");
Core::Module("List");
Core::Module("Find");

Core::scanConfig();


while(true)
{
	Core::Tick();
	Core::Socket();

	usleep(BOT_TICKRATE);
}
