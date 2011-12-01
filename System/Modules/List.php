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
