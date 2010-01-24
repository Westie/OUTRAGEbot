<?php
/**
 *	Definitions for OUTRAGEbot
 *
 *	@package OUTRAGEbot-RC5
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0.0-RC5
 */


/* Some error reporting! */
error_reporting(E_ALL | E_STRICT);


/* Some bot-brag-relating things. */
define("BOT_VERSION", "1.0.0-RC5");
define("BOT_RELDATE", "22/01/2010 22:30:00");


/* How long the bot sleeps between socket calls. */
define("CORE_SLEEP", 50000);


/* IRC characters */
define("IRC_EOL", "\r\n");


/* The ways of the bots of sending messages. */
define("SEND_DEF", 0);
define("SEND_MAST", 1);
define("SEND_CURR", 2);
define("SEND_DIST", 4);
define("SEND_ALL", 8);


/* Depreciated, but kept for compatibility. It's the old version of the above. */
define("sendDefined", 0);
define("sendMaster", 1);
define("sendCurrent", 2);
define("sendDistribution", 4);
define("sendAll", 8);


/* Channel/user modes. */
define("MODE_USER_VOICE", 1);
define("MODE_USER_HOPER", 2);
define("MODE_USER_OPER", 4);
define("MODE_USER_ADMIN", 8);
define("MODE_USER_OWNER", 16);
