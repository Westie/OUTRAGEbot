<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     95e273100e115ed48f7d6cc58cb28dceaded9c3c
 *	Committed at:   Sun Jan 30 19:34:48 2011 +0000
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
	static function initModule()
	{
		Core::introduceFunction("getChannelList", array(__CLASS__, "sendList"));
	}


	/**
	 *	The command handler
	 */
	static function sendList()
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
	static function parseLineResponse($sString)
	{
		$pMessage = Core::getMessageObject($sString);

		switch($pMessage->Numeric)
		{
			case "321":
			{
				return false;
			}

			case "322":
			{
				self::$pTempObject[] = (object) array
				(
					"channel" => $pMessage->Parts[3],
					"count" => $pMessage->Parts[4],
					"topic" => $pMessage->Payload,
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
