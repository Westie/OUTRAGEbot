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


class ModuleWho
{
	static
		$pTempObject = null;


	/**
	 *	Called when the module is loaded.
	 */
	public static function initModule()
	{
		$aMethods = array
		(
			"who",
			"getWhoList",
		);

		Core::introduceFunction($aMethods, array(__CLASS__, "requestWho"));
	}


	/**
	 *	The command handler
	 */
	public static function requestWho($sQuery, $bOnlyOperators = false)
	{
		self::$pTempObject = array();

		$pInstance = Core::getCurrentInstance();
		$pSocket = $pInstance->getCurrentSocket();

		$cOperators = "";

		if($bOnlyOperators)
		{
			$cOperators = " o";
		}

		$pSocket->Output("WHO {$sQuery}{$cOperators}");
		$pSocket->executeCapture(array(__CLASS__, "parseLineResponse"));

		return self::$pTempObject;
	}


	/**
	 *	Parses the input - and I'm doing it this way to save cycles.
	 *	Why process everything twice?
	 */
	public static function parseLineResponse($sString)
	{
		$pMessage = Core::getMessageObject($sString);

		switch($pMessage->Numeric)
		{
			case "352":
			{
				$aInformation = explode(' ', $pMessage->Payload, 2);

				self::$pTempObject[] = (object) array
				(
					"nickname" => $pMessage->Parts[7],
					"username" => $pMessage->Parts[4],
					"address" => $pMessage->Parts[5],
					"realname" => $aInformation[1],
					"channel" => $pMessage->Parts[3],
					"server" => $pMessage->Parts[6],
					"userModes" => $pMessage->Parts[8],
					"serverHops" => $aInformation[0],
				);

				$pMask = (object) array
				(
					"Nickname" => $pMessage->Parts[7],
					"Username" => $pMessage->Parts[4],
					"Hostname" => $pMessage->Parts[5],
				);

				$pInstance = Core::getCurrentInstance();
				$pInstance->getUser($pMask);

				$pChannel = $pInstance->getChannel($pMessage->Parts[3]);
				$pChannel->addUserToChannel($pMessage->Parts[7], null);

				return false;
			}

			case "315":
			{
				return true;
			}

			return false;
		}
	}
}
