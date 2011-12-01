<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     4a7dced0b3ef96338f36bc64bd40ed91063c3e01
 *	Committed at:   Thu Dec  1 22:49:57 GMT 2011
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
		$aMethods = array
		(
			"whois",
			"getWhois",
			"getWhoisData",
		);

		Core::introduceFunction($aMethods, array(__CLASS__, "requestWhois"));
	}


	/**
	 *	The command handler
	 */
	static function requestWhois($sNickname)
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
