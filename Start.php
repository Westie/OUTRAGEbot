<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     b4261585b7804e8c46a15f36d4cb274a811f0586
 *	Committed at:   Mon Aug 29 23:47:15 BST 2011
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
Core::Module("Whois");
Core::Module("List");

Core::scanConfig();


while(true)
{
	Core::Tick();
	Core::Socket();

	usleep(BOT_TICKRATE);
}
