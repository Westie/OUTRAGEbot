<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     aa20fb1ab134d3c23a4b608430d13ef7178b0630
 *	Committed at:   Sat Feb 19 11:56:18 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


/**
 *	Setting the error reporting.
 */
error_reporting(E_ALL | E_STRICT);


/**
 *	Set the tick rate.
 */
define("BOT_TICKRATE", 2600);


/**
 *	Some bot-brag-relating things.
 */
define("BOT_VERSION", "2.0.0-Alpha-aa20fb1");
define("BOT_RELDATE", "");


/**
 *	The ways of the bots of sending messages.
 */
define("SEND_DEF", 0x0);
define("SEND_MAST", 0x1);
define("SEND_CURR", 0x2);
define("SEND_DIST", 0x4);
define("SEND_ALL", 0x8);


/**
 *	Message modifier definitions
 */
define("FORMAT", 0x20);


/**
 *	Channel/user modes.
 */
define("MODE_USER_VOICE", 1);
define("MODE_USER_HOPER", 2);
define("MODE_USER_OPER", 4);
define("MODE_USER_ADMIN", 8);
define("MODE_USER_OWNER", 16);


/**
 *	A little trick to do with event handlers.
 */
define("END_EVENT_EXEC", 0x80000000);
