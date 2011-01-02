<?php
/**
 *	OUTRAGEbot development
 */


/**
 *	Setting the error reporting.
 */
error_reporting(E_ALL | E_STRICT);


/**
 *	Some bot-brag-relating things.
 */
define("BOT_VERSION", "2.0.0-Alpha-0");
define("BOT_RELDATE", "-today-");


/**
 *	The ways of the bots of sending messages.
 */
define("SEND_DEF", 0);
define("SEND_MAST", 1);
define("SEND_CURR", 2);
define("SEND_DIST", 4);
define("SEND_ALL", 8);


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