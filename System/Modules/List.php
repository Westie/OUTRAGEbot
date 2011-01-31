<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     5e4fff1d09af5aaa4db1671275cf3dd47a978e4c
 *	Committed at:   Mon Jan 31 09:41:38 GMT 2011
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
		Core::introduceFunction("getChannelList", array(__CLASS__, "sendList"));
	}


	/**
	 *	The command handler
	 */
	public static function sendList()
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
