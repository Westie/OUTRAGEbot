<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     7d3682aeeb48655779d4f6e1748e427c07f8efe7
 *	Committed at:   Fri May 20 18:01:06 BST 2011
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
Core::Library("Resource");
Core::Library("Script");
Core::Library("Socket");
Core::LModule("Timer");
Core::Library("Utilities");


Core::Module("Whois");
Core::Module("List");

Core::scanConfig();


error_reporting(E_ALL | E_STRICT);


while(true)
{
	Core::Tick();
	Core::Socket();

	usleep(BOT_TICKRATE);
}
