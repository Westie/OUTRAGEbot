<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     09c68fbaed58f5eaf8f1066c15fd6277f02d8812
 *	Committed at:   Sat Nov 26 19:53:04 GMT 2011
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

				$pMask = CoreMaster::parseHostmask($pMessage->Parts[5]);

				$pInstance = Core::getCurrentInstance();
				$pInstance->getUser($pMask);

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
