<?php
/**
 *	Definitions for OUTRAGEbot
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC3 (Git commit: 79e2d1487a9d84116719f429079092c72cf417e9)
 */


/* Some error reporting! */
error_reporting(E_ALL | E_STRICT);


/* Some bot-brag-relating things. */
define("BOT_VERSION", "1.1.1-RC3-79e2d14");
define("BOT_RELDATE", "11/07/2010");


/* How long the bot sleeps between socket calls. */
define("CORE_SLEEP", 70000);


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
