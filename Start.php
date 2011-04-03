<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     73c42fabc1ae1f4549ff642c290009d002d4e51e
 *	Committed at:   Sun Apr  3 12:07:44 BST 2011
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
Core::Module("Console");


Core::scanConfig();


while(true)
{
	Core::Tick();
	Core::Socket();

	usleep(BOT_TICKRATE);
}
