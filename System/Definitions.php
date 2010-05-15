<?php
/**
 *	Definitions for OUTRAGEbot
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.0
 */


/* Some error reporting! */
error_reporting(E_ALL | E_STRICT);


/* Some bot-brag-relating things. */
define("BOT_VERSION", "1.1.0");
define("BOT_RELDATE", "15/05/2010 17:35");


/* How long the bot sleeps between socket calls. */
define("CORE_SLEEP", 25000);


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


/* Handler definitions */
define("IRC_HOSTMASK", 0);
define("IRC_COMMAND", 1);

define("IRC_PRIVMSG_CHANNEL", 2);
define("IRC_PRIVMSG_MESSAGE", 3);

define("IRC_INVITE_CHANNEL", 3);
define("IRC_INVITE_NICKNAME", 2);

define("IRC_JOIN_CHANNEL", -1);
define("IRC_JOIN_NICKNAME", -1); 
