<?php
/**
 *	OUTRAGEbot development
 */


class CoreHandler
{
	/**
	 *	Called when there are no available handlers for a specific numeric.
	 */
	static function Unhandled(CoreMaster $pInstance, $pMessage)
	{
		println(" * {$pMessage->Raw}");
		
		switch($pMessage->Numeric)
		{
			case "001":
			{
				return;
			}
		}
	}
	
	
	/**
	 *	Called when a message is sent to the user.
	 */
	static function Privmsg(CoreMaster $pInstance, $pMessage)
	{
		if($pMessage->Payload[0] == Format::CTCP)
		{
			$pInstance->triggerEvent("onCTCPRequest", $pMessage->User->Nickname, $pMessage->Parts[2], substr($pMessage->Payload, 1, -1));
			return;
		}
		
		switch($pMessage->Parts[2][0])
		{
			case '#':
			case '&':
			case '~':
			case '*':
			{
				if($pMessage->Parts[3][0] == $pInstance->pConfig->Network->delimiter)
				{	
					$aCommandPayload = explode(' ', substr($pMessage->Payload, 1), 2);
					
					if(!isset($aCommandPayload[1]))
					{
						$aCommandPayload[1] = "";
					}
					
					return $pInstance->triggerEvent("onChannelMessage", $pMessage->Parts[2], $pMessage->User->Nickname, $aCommandPayload[0], $aCommandPayload[1]);
				}
				
				return $pInstance->triggerEvent("onChannelMessage", $pMessage->Parts[2], $pMessage->User->Nickname, $pMessage->Payload);
			}
			default:
			{
				return $pInstance->triggerEvent("onPrivateMessage", $pMessage->User->Nickname, $pMessage->Parts[2], $pMessage->Payload);
			}
		}
	}
}