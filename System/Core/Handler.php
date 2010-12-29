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
		}
	}
	
	
	/**
	 *	Called when the bot connects to the network.
	 *	Numeric: 001 - Successful connection.
	 */
	static function N001(CoreMaster $pInstance, $pMessage)
	{
		$pNetwork = $pInstance->pConfig->Network;
		
		foreach($pNetwork->perform as $sPerformString)
		{
			$pInstance->pSocket->Output($sPerformString);
		}
		
		foreach($pNetwork->channelArray as $sChannel)
		{
			$pInstance->pSocket->Output("JOIN {$sChannel}");
		}
		
		$pInstance->triggerEvent("onConnect");
	}
	
	
	/**
	 *	Called when a user joins a channel.
	 */
	static function Join(CoreMaster $pInstance, $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[2]);
		
		$pChannel->addUserToChannel($pMessage->User->Nickname);
		$pInstance->triggerEvent("onJoin", $sNickname, $pChannel);
	}
	
	
	/**
	 *	Called when recieving the server settings.
	 *	Numeric: 005 - Server settings and capabilities.
	 */
	static function N005(CoreMaster $pInstance, $pMessage)
	{
		$aParts = $pMessage->Parts;
		
		array_shift($aParts);
		array_shift($aParts);
		array_shift($aParts);
		
		foreach($aParts as $sPart)
		{
			if($sPart[0] == ':')
			{
				break;
			}
			
			$aCommands = explode("=", $sPart, 2);
			
			if(empty($aCommands[1]))
			{
				$aCommands[1] = true;
			}
			
			$pInstance->pConfig->Server->{$aCommands[0]} = $aCommands[1];
		}
		
		if(!empty($pInstance->pConfig->Server->NAMEX))
		{
			$pInstance->pSocket->Output("PROTOCTL NAMESX");
		}
	}
	
	
	/**
	 *	Called when we recieve a NAMES response.
	 *	Numeric: 353 - Names response.
	 */
	static function N353(CoreMaster $pInstance, $pMessage)
	{
		$aUserList = explode(' ', trim($pMessage->Payload));
		
		$pChannel = $pInstance->getChannel($pMessage->Parts[4]);
		
		foreach($aUserList as $sNickname)
		{
			preg_match("/[+%@&~]/", trim($sNickname), $aChannelModes);
			
			$sChannelMode = CoreUtilities::modeCharToLetter(implode("", $aChannelModes));
			
			$sNickname = preg_replace("/[+%@&~]/", "", $sNickname);
			$pChannel->addUserToChannel($sNickname, $sChannelMode);
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