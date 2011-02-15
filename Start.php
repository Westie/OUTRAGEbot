<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     e75544e55f1917e98a40c6eabfd2a530262ab803
 *	Committed at:   Tue Feb 15 22:05:13 GMT 2011
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
Core::Module("Console");


Core::scanConfig();


while(true)
{
	Core::Tick();
	Core::Socket();

	usleep(BOT_TICKRATE);
}
