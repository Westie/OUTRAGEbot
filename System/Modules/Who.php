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
