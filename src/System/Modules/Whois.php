<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     6fa977b99b0dae9e08284c0eb7eef0ed021d9ed8
 *	Committed at:   Sun Jan  1 22:50:25 GMT 2012
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
			"address" => null,
			"away" => null,
			"channels" => array(),
			"helper" => null,
			"idleTime" => 0,
			"ircOp" => null,
			"nickname" => null,
			"realname" => null,
			"serverAddress" => null,
			"serverName" => null,
			"signonTime" => 0,
			"username" => null,
			"isSecure" => false,
			"ipAddress" => null,
			"userModes" => null,
			"serverModes" => null,
		);

		$pInstance = Core::getCurrentInstance();
		$pSocket = $pInstance->getCurrentSocket();

		$pSocket->Output("WHOIS {$sNickname} {$sNickname}"); // We"re cheating here!
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
				self::$pTempObject->channels = array_merge(self::$pTempObject->channels, explode(" ", $pMessage->Payload));

				return false;
			}

			case "378":
			{
				$aMatches = array();

				if(preg_match("/^is connecting from (.*?) (.*?)$/", $pMessage->Payload, $aMatches))
				{
					self::$pTempObject->ipAddress = $aMatches[2];
				}

				return false;
			}

			case "379":
			{
				if(preg_match("/^is using modes (.*?) (.*?)$/", $pMessage->Payload, $aMatches))
				{
					self::$pTempObject->userModes = $aMatches[1];
					self::$pTempObject->serverModes = $aMatches[2];
				}

				return false;
			}

			case "671":
			{
				self::$pTempObject->isSecure = true;
			}
		}

		return false;
	}
}
