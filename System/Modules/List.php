<?php
/**
 *	OUTRAGEbot development
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
		
		$pSocket->Output("WHOIS {$sNickname}");
		$pSocket->executeCapture(array(__CLASS__, "parseLineResponse"));
		
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
				// :irc Westie 232 #channel count :topic
				
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