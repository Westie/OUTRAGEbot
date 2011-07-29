<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     eac797d84cc931461b50efc88b3a854041862620
 *	Committed at:   Fri Jul 29 19:18:32 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreHandler
{
	/**
	 *	Called when there are no available handlers for a specific numeric.
	 */
	public static function Unhandled(CoreMaster $pInstance, MessageObject $pMessage)
	{
		switch($pMessage->Numeric)
		{
			case "001":
			{
				return self::onConnect($pInstance, $pMessage);
			}

			case "433":
			{
				return self::onNicknameConflict($pInstance, $pMessage);
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
	public static function onConnect(CoreMaster $pInstance, MessageObject $pMessage)
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
	public static function N005(CoreMaster $pInstance, MessageObject $pMessage)
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
	 *	Called when the bot requests MODE information for that channel.
	 *	I'm presuming that whenever this is requested, it's full of the
	 *	revelent data.
	 *
	 *	Numeric: 324 - Channel modes.
	 */
	public static function N324(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$aParts = $pMessage->Parts;

		array_shift($aParts);
		array_shift($aParts);
		array_shift($aParts);

		$pInstance->getChannel($aParts[0])->pModes = new stdClass();

		return self::parseModeString($pInstance, $aParts);
	}


	/**
	 *	Called when the bot requests MODE information for that channel.
	 *	Numeric: 329 - Channel information.
	 */
	public static function N329(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$aParts = $pMessage->Parts;

		array_shift($aParts);
		array_shift($aParts);
		array_shift($aParts);

		$pInstance->getChannel(array_shift($aParts))->iCreateTime = array_shift($aParts);
	}


	/**
	 *	Called when a user enters a channel.
	 *	Numeric: 332 - Topic string.
	 */
	public static function N332(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$pInstance->getChannel($pMessage->Parts[3])->pTopic->topicString = $pMessage->Payload;
	}


	/**
	 *	Called when a user enters a channel.
	 *	Numeric: 332 - Topic information.
	 */
	public static function N333(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[3]);

		$pChannel->pTopic->topicSetter = $pMessage->Parts[4];
		$pChannel->pTopic->topicTime = $pMessage->Parts[5];
	}


	/**
	 *	Called when we recieve a NAMES response.
	 *	Numeric: 353 - Names response.
	 */
	public static function N353(CoreMaster $pInstance, MessageObject $pMessage)
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
	public static function Join(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$sChannelName = substr($pMessage->Parts[2], 1);
		$pChannel = $pInstance->getChannel($sChannelName);

		if($pInstance->pSocket->pConfig->nickname == $pMessage->User->Nickname)
		{
			$pInstance->Raw("MODE {$sChannelName}");
		}

		$pChannel->addUserToChannel($pMessage->User->Nickname);
		$pInstance->triggerEvent("onChannelJoin", $pChannel, $pMessage->User->Nickname);
	}


	/**
	 *	Called when a user leaves a channel.
	 */
	public static function Part(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[2]);

		$pChannel->removeUserFromChannel($pMessage->User->Nickname);
		$pInstance->triggerEvent("onChannelPart", $pChannel, $pMessage->User->Nickname, $pMessage->Payload);
	}


	/**
	 *	Called when a user is forcibly removed from a channel.
	 *	Sorry, I mean kicked.
	 */
	public static function Kick(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[2]);

		$pChannel->removeUserFromChannel($pMessage->Parts[3]);
		$pInstance->triggerEvent("onChannelKick", $pChannel, $pMessage->User->Nickname, $pMessage->Parts[3], $pMessage->Payload);
	}


	/**
	 *	Called when a user changes their nickname.
	 */
	public static function Nick(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$sNewNickname = $pMessage->Parts[2];

		if($sNewNickname[0] == ':')
		{
			$sNewNickname = substr($sNewNickname, 1);
		}

		foreach($pInstance->pChannels as $pChannel)
		{
			$pChannel->renameUserInChannel($pMessage->User->Nickname, $sNewNickname);
		}

		if($pInstance->pSocket->pConfig->nickname == $pMessage->User->Nickname)
		{
			$pInstance->pSocket->pConfig->nickname = $sNewNickname;
		}

		$pInstance->triggerEvent("onNicknameChange", $pMessage->User->Nickname, $sNewNickname);
	}


	/**
	 *	Called when a user quits from the network.
	 */
	public static function Quit(CoreMaster $pInstance, MessageObject $pMessage)
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
	public static function Notice(CoreMaster $pInstance, MessageObject $pMessage)
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
	public static function Privmsg(CoreMaster $pInstance, MessageObject $pMessage)
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
	public static function Topic(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$pChannel = $pInstance->getChannel($pMessage->Parts[2]);

		$pChannel->pTopic = (object) array
		(
			"topicString" => $pMessage->Payload,
			"topicTime" => time(),
			"topicSetter" => $pMessage->User->Nickname,
		);

		$pInstance->triggerEvent("onChannelTopic", $pChannel, $pMessage->User->Nickname, $pMessage->Payload);
	}


	/**
	 *	Called when the mode is changed in the channel.
	 *
	 *	Yeah, no need for any other methods, just huge variables.
	 *	I might seperate this and make it useable later.
	 */
	public static function Mode(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$aParts = $pMessage->Parts;

		array_shift($aParts);
		array_shift($aParts);

		return self::parseModeString($pInstance, $aParts);
	}


	/**
	 *	It's easier maintaining one bit of code, than keeping two
	 *	bits.
	 */
	private static function parseModeString($pInstance, $aParts)
	{
		$aModes = array();
		$pServerConfig = $pInstance->pConfig->Server;

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

				++$iGroup;
			}

			$iGroupID = $bIsPrefix ? 2 : $iGroupID;
			$pChannel = $pInstance->getChannel($sChannel);

			switch($iGroupID)
			{
				/* Arguments required at all times */
				case 1:
				case 2:
				{
					$sArgument = array_shift($aParts);

					if(preg_match('/['.$pServerConfig->PREFIX.']/', $cMode))
					{
						$pChannel->modifyUserInChannel($sArgument, ($iSet == 1 ? '+' : '-'), $cMode);
						break;
					}

					$pChannel->pModes->$cMode = $sArgument;
					break;
				}

				/* Arguments passed only if enabled/changed */
				case 3:
				{
					if($iSet == 1)
					{
						$pChannel->pModes->$cMode = array_shift($aParts);
						break;
					}

					$pChannel->pModes->$cMode = false;
					break;
				}

				/* No passed arguments */
				case 4:
				{
					$pChannel->pModes->$cMode = ($iSet == 1);
					break;
				}
			}
		}
	}


	/**
	 *	Alright, I'm cheating with the CTCP stuff.
	 */
	public static function CTCP(CoreMaster $pInstance, MessageObject $pMessage)
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
				return $pInstance->Notice($pMessage->User->Nickname, chr(1)."VERSION {$pInstance->pConfig->Network->version}".chr(1));
			}

			case "TIME":
			{
				return $pInstance->Notice($pMessage->User->Nickname, chr(1)."TIME ".date("d/m/Y H:i:s", time()).chr(1));
			}

			case "PING":
			{
				return $pInstance->Notice($pMessage->User->Nickname, chr(1)."PING {$aCTCP[1]}".chr(1));
			}

			case "UPTIME":
			{
				$aDuration = CoreUtilities::Duration($pInstance->pSocket->pConfig->StartTime);
				$sString = "UPTIME {$aDuration['weeks']} weeks, {$aDuration['days']} days, {$aDuration['hours']} hours, {$aDuration['minutes']} minutes, {$aDuration['seconds']} seconds.";

				return $pInstance->Notice($pMessage->User->Nickname, chr(1).$sString.chr(1));
			}

			case "START":
			{
				return $pInstance->Notice($pMessage->User->Nickname, chr(1)."START ".date("d/m/Y H:i:s", time($pInstance->pSocket->pConfig->StartTime)).chr(1));
			}
		}
	}


	/**
	 *	Called when there's a ping response.
	 */
	public static function Pong(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$pInstance->pSocket->iPingMiss = false;
	}


	/**
	 *	Called when there's an error - when someone decides to disconnect the bot.
	 */
	public static function onServerError(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$pSocket = $pInstance->pSocket;

		$pSocket->destroyConnection();
		$pInstance->triggerEvent("onServerError", $pMessage->Payload);

		CoreTimer::Add(array($pSocket, "createConnection"), 3);
	}


	/**
	 *	Called when there's an error - when someone decides to disconnect the bot.
	 */
	public static function onNicknameConflict(CoreMaster $pInstance, MessageObject $pMessage)
	{
		$sNewNickname = $pInstance->pSocket->pConfig->altnick.mt_rand(10, 99);
		$pInstance->pSocket->setSocketNickname($sNewNickname);

		$pInstance->triggerEvent("onNicknameConflict", $pMessage->sNewNickname);
	}
}
