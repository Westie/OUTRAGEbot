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
		switch($pMessage->Numeric)
		{
			case "001":
			{
				self::onConnect($pInstance, $pMessage);
			}
		}
	}
	
	
	/**
	 *	Called when the bot connects to the network.
	 */
	static function onConnect(CoreMaster $pInstance, $pMessage)
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
			
			if($aCommands[0] == "CHANMODES")
			{				
				$pInstance->pConfig->Server->ChannelModes = explode(',', $aCommands[1]);
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
	 *	Called when a user joins a channel.
	 */
	static function Join(CoreMaster $pInstance, $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[2]);
		
		$pChannel->addUserToChannel($pMessage->User->Nickname);
		$pInstance->triggerEvent("onJoin", $pMessage->User->Nickname, $pChannel);
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
	
	
	/**
	 *	Called when the mode is changed in the channel.
	 *
	 *	Yeah, no need for any other methods, just huge variables.
	 *	I might seperate this and make it useable later.
	 */
	static function Mode(CoreMaster $pInstance, $pMessage)
	{
		println("\r\n\r\n-- {$pMessage->Raw}");
		
		$aParts = $pMessage->Parts;
		$aModes = array();
		
		$pServerConfig = $pInstance->pConfig->Server;
		
		array_shift($aParts);
		array_shift($aParts);
		
		$sChannel = array_shift($aParts);
		$sSetting = array_shift($aParts);
		
		$iSet = 0;
		
		$iLength = strlen($sSetting);
		$iSwitches = 0;
		
		for($i = 0; $i < $iLength; ++$i)
		{
			$cMode = $sSetting[$i];
			
			switch($cMode)
			{
				case '+':
				{
					$iSet = 1;
					continue;
				}
				case '-':
				{
					$iSet = 2;
					continue;
				}
			}
			
			if($iSet == 0 || $cMode == '+' || $cMode == '-')
			{
				continue;
			}
			
			$bIsPrefix = strpos($pServerConfig->PREFIX, $cMode) !== false;
			$iGroupID = 0;
			
			foreach($pServerConfig->ChannelModes as $iGroup => $sGroupString)
			{
				if(strpos($sGroupString, $cMode) === false)
				{
					continue;
				}
				
				$iGroupID = $iGroup + 1;
			}
			
			$iGroupID = $bIsPrefix ? 2 : $iGroupID;
			
			switch($iGroupID)
			{
				/* Arguments required at all times */
				case 1:
				case 2:
				{
					$sArgument = array_shift($aParts);
				
					if(preg_match('/['.$pServerConfig->PREFIX.']/', $cMode))
					{
						$pChannel = $pInstance->getChannel($sChannel);						
						$pChannel->modifyUserInChannel($sArgument, ($iSet == 1 ? '+' : '-'), $cMode);
					}
					
					break;
				}
				
				/* Arguments passed only if enabled/changed */
				case 3:
				{
					if($iSet == 1)
					{
						array_shift($aParts);
					}
					
					break;
				}
				
				/* No passed arguments */
				case 4:
				{
					break;
				}
			}
		}
	}
}