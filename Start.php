<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     13bf222a39ee97675d5c82534ea6e3f7d465a6c8
 *	Committed at:   Sun Jan 30 19:11:03 2011 +0000
 *
 *	Licence:	http://www.typefish.co.uk/licences/
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
Core::Library("Resources");
Core::Library("Script");
Core::Library("Socket");
Core::LModule("Timer");
Core::Library("Utilities");


Core::Module("Whois");
Core::Module("List");


Core::scanConfig();


while(true)
{
	Core::Tick();
	Core::Socket();

	usleep(BOT_TICKRATE);
}
