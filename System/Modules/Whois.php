<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     31ad7f1e21fb1a1676f99c6ce89e2e51a6897a0e
 *	Committed at:   Wed Aug 31 21:37:31 UTC 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class ModuleWhois
{
	static
		$pTempObject = null;


	/**
	 *	Called when the module is loaded.
	 */
	static function initModule()
	{
		Core::introduceFunction("getWhois", array(__CLASS__, "sendWhoisRequest"));
		Core::introduceFunction("getWhoisData", array(__CLASS__, "sendWhoisRequest"));
	}


	/**
	 *	The command handler
	 */
	static function sendWhoisRequest($sNickname)
	{
		/* Send the request, and sort out the handler */
		self::$pTempObject = (object) array
		(
			'address' => false,
			'away' => false,
			'channels' => array(),
			'helper' => false,
			'idleTime' => 0,
			'ircOp' => false,
			'nickname' => false,
			'realname' => false,
			'serverAddress' => false,
			'serverName' => false,
			'signonTime' => 0,
			'username' => false,
		);

		$pInstance = Core::getCurrentInstance();
		$pSocket = $pInstance->getCurrentSocket();

		$pSocket->Output("WHOIS {$sNickname} {$sNickname}"); // We're cheating here!
		$pSocket->executeCapture(array(__CLASS__, "parseWhoisLine"));

		return self::$pTempObject;
	}


	/**
	 *	Parses the input
	 */
	static function parseWhoisLine($sString)
	{
		$pMessage = Core::getMessageObject($sString);

		switch($pMessage->Numeric)
		{
			case "301":
			{
				self::$pTempObject->away = $pMessage->Payload;

				return false;
			}

			case "310":
			{
				self::$pTempObject->helper = true;

				return false;
			}

			case "311":
			{
				self::$pTempObject->nickname = $pMessage->Parts[3];
				self::$pTempObject->username = $pMessage->Parts[4];
				self::$pTempObject->address = $pMessage->Parts[5];
				self::$pTempObject->realname = $pMessage->Payload;

				return false;
			}

			case "312":
			{
				self::$pTempObject->serverAddress = $pMessage->Parts[4];
				self::$pTempObject->serverName = $pMessage->Payload;

				return false;
			}

			case "313":
			{
				self::$pTempObject->ircOp = true;

				return false;
			}

			case "317":
			{
				self::$pTempObject->idleTime = $pMessage->Parts[4];
				self::$pTempObject->signonTime = $pMessage->Parts[5];

				return false;
			}

			case "318":
			{
				return true;
			}

			case "319":
			{
				self::$pTempObject->channels = array_merge(self::$pTempObject->channels, explode(' ', $pMessage->Payload));

				return false;
			}
		}
	}
}
