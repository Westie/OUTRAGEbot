<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     a29fdc0da8885075d18511f41860d97c3923a140
 *	Committed at:   Sun, 30 Jan 2011 17:12:24 +0000
 *
 *	Licence:	http://www.typefish.co.uk/licences/
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
		
		if(!$pInstance->pSocket->isSocketSlave())
		{
			$pInstance->triggerEvent("onUnhandledEvent", $pMessage);
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
		
		if(!$pInstance->pSocket->isSocketSlave())
		{
			$pInstance->triggerEvent("onConnect");
		}
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
	 *	Called when a user enters a channel.
	 *	Numeric: 332 - Topic string.
	 */
	static function N332(CoreMaster $pInstance, $pMessage)
	{
		$pInstance->getChannel($pMessage->Parts[3])->pTopic->chantopic = $pMessage->Payload;
	}
	
	
	/**
	 *	Called when a user enters a channel.
	 *	Numeric: 332 - Topic information.
	 */
	static function N333(CoreMaster $pInstance, $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[3]);
		
		$pChannel->pTopic->setter = $pMessage->Parts[4];
		$pChannel->pTopic->timestamp = $pMessage->Parts[5];
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
		$pChannel = $pInstance->getChannel(substr($pMessage->Parts[2], 1));
		
		$pChannel->addUserToChannel($pMessage->User->Nickname);
		$pInstance->triggerEvent("onChannelJoin", $pChannel, $pMessage->User->Nickname);
	}
	
	
	/**
	 *	Called when a user leaves a channel.
	 */
	static function Part(CoreMaster $pInstance, $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[2]);
		
		$pChannel->removeUserFromChannel($pMessage->User->Nickname);
		$pInstance->triggerEvent("onChannelPart", $pChannel, $pMessage->User->Nickname);
	}
	
	
	/**
	 *	Called when a user is forcibly removed from a channel.
	 *	Sorry, I mean kicked.
	 */
	static function Kick(CoreMaster $pInstance, $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[2]);
		
		$pChannel->removeUserFromChannel($pMessage->Parts[3]);
		$pInstance->triggerEvent("onChannelKick", $pChannel, $pMessage->User->Nickname, $pMessage->Parts[3], $pMessage->Payload);
	}
	
	
	/**
	 *	Called when a user changes their nickname.
	 */
	static function Nick(CoreMaster $pInstance, $pMessage)
	{
		/* TODO: Add the self-check for force nick change. */
		foreach($pInstance->pChannels as $pChannel)
		{
			$pChannel->renameUserInChannel($pMessage->Payload, $pMessage->User->Nickname);
		}
		
		$pInstance->triggerEvent("onNicknameChange", $pMessage->Payload, $pMessage->User->Nickname);
	}
	
	
	/**
	 *	Called when a user quits from the network.
	 */
	static function Quit(CoreMaster $pInstance, $pMessage)
	{
		foreach($pInstance->pChannels as $pChannel)
		{
			$pChannel->removeUserFromChannel($pMessage->User->Nickname);
		}
		
		$pInstance->triggerEvent("onUserQuit", $pMessage->User->Nickname, $pMessage->Payload);
	}
	
	
	/**
	 *	Called when a notice is recieved.
	 */
	static function Notice(CoreMaster $pInstance, $pMessage)
	{
		if($pMessage->Payload[0] == Format::CTCP)
		{
			$pInstance->triggerEvent("onCTCPResponse", $pMessage->User->Nickname, substr($pMessage->Payload, 1, -1));
			return;
		}
		
		return $pInstance->triggerEvent("onUserNotice", $pMessage->User->Nickname, $pMessage->Parts[2], $pMessage->Payload);
	}
	
	
	/**
	 *	Called when a message is sent to the user.
	 */
	static function Privmsg(CoreMaster $pInstance, $pMessage)
	{
		if($pMessage->Payload[0] == Format::CTCP)
		{
			self::CTCP($pInstance, $pMessage);
			$pInstance->triggerEvent("onCTCPRequest", $pMessage->User->Nickname, substr($pMessage->Payload, 1, -1));
			
			return;
		}
		
		switch($pMessage->Parts[2][0])
		{
			case '#':
			case '&':
			case '~':
			case '*':
			{
				$pChannel = $pInstance->getChannel($pMessage->Parts[2]);
				
				if($pMessage->Payload[0] == $pInstance->pConfig->Network->delimiter)
				{	
					$aCommandPayload = explode(' ', substr($pMessage->Payload, 1), 2);
					
					if(!isset($aCommandPayload[1]))
					{
						$aCommandPayload[1] = "";
					}
					
					return $pInstance->triggerEvent("onChannelCommand", $pChannel, $pMessage->User->Nickname, $aCommandPayload[0], $aCommandPayload[1]);
				}
				
				return $pInstance->triggerEvent("onChannelMessage", $pChannel, $pMessage->User->Nickname, $pMessage->Payload);
			}
			default:
			{
				return $pInstance->triggerEvent("onPrivateMessage", $pMessage->User->Nickname, $pMessage->Parts[2], $pMessage->Payload);
			}
		}
	}
	
	
	/**
	 *	Called when a user changes the topic.
	 */
	static function Topic(CoreMaster $pInstance, $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[3]);
		
		$pChannel->pTopic->chantopic = $pMessage->Payload;
		$pChannel->pTopic->timestamp = time();
		$pChannel->pTopic->setter = $pMessage->User->Nickname;
		
		$pInstance->triggerEvent("onChannelTopic", $pChannel, $pMessage->User->Nickname, $pMessage->Payload);
	}
	
	
	/**
	 *	Called when the mode is changed in the channel.
	 *
	 *	Yeah, no need for any other methods, just huge variables.
	 *	I might seperate this and make it useable later.
	 */
	static function Mode(CoreMaster $pInstance, $pMessage)
	{
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
	
	
	/**
	 *	Alright, I'm cheating with the CTCP stuff.
	 */
	static function CTCP(CoreMaster $pInstance, $pMessage)
	{
		$pMessage->Payload = substr($pMessage->Payload, 1, -1);
		
		$aCTCP = explode(' ', $pMessage->Payload, 2);
		
		if(empty($aCTCP[1]))
		{
			$aCTCP[1] = "";
		}
		
		switch($aCTCP[0])
		{
			case "VERSION":
			{
				return $pInstance->ctcpReply($pMessage->User->Nickname, "VERSION {$pInstance->pConfig->Network->version}");
			}
			
			case "TIME":
			{
				return $pInstance->ctcpReply($pMessage->User->Nickname, "TIME ".date("d/m/Y H:i:s", time()));
			}
			
			case "PING":
			{
				return $pInstance->ctcpReply($pMessage->User->Nickname, "PING {$aCTCP[1]}");
			}
			
			case "UPTIME":
			{
				$aDuration = CoreUtilities::Duration($pInstance->pSocket->pConfig->StartTime);
				$sString = "UPTIME {$aDuration['weeks']} weeks, {$aDuration['days']} days, {$aDuration['hours']} hours, {$aDuration['minutes']} minutes, {$aDuration['seconds']} seconds.";
				
				return $pInstance->ctcpReply($pMessage->User->Nickname, $sString);
			}
			
			case "START":
			{
				return $pInstance->ctcpReply($pMessage->User->Nickname, "START ".date("d/m/Y H:i:s", time($pInstance->pSocket->pConfig->StartTime)));
			}
		}
	}
}