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


class ModuleList
{
	static
		$pTempObject = null;


	/**
	 *	Called when the module is loaded.
	 */
	public static function initModule()
	{
		Core::introduceFunction("getChannelList", array(__CLASS__, "requestList"));
	}


	/**
	 *	The command handler
	 */
	public static function requestList()
	{
		self::$pTempObject = array();

		$pInstance = Core::getCurrentInstance();
		$pSocket = $pInstance->getCurrentSocket();

		$pSocket->Output("LIST");
		$pSocket->executeCapture(array(__CLASS__, "parseLineResponse"));

		usort(self::$pTempObject, function($pChannelA, $pChannelB)
		{
			if($pChannelA->count == $pChannelB->count)
			{
				return 0;
			}

			return ($pChannelA->count < $pChannelB->count) ? 1 : -1;
		});

		return self::$pTempObject;
	}


	/**
	 *	Parses the input
	 */
	public static function parseLineResponse($sString)
	{
		$pMessage = new MessageObject($sString);

		switch($pMessage->Numeric)
		{
			case "321":
			{
				return false;
			}

			case "322":
			{
				$aList = explode(' ', $pMessage->Payload, 2);

				self::$pTempObject[] = (object) array
				(
					"channel" => $pMessage->Parts[3],
					"count" => $pMessage->Parts[4],
					"modes" => substr($aList[0], 1, -1),
					"topic" => $aList[1],
				);

				return false;
			}

			case "323":
			{
				return true;
			}

			return false;
		}
	}
}
