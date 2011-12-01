<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Beta

 *	Git commit:     a27156d898fcdbfad3f997382f35b1887b317e02
 *	Committed at:   Thu Dec  1 22:14:49 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreMaster
{
	/**
	 *	Required variables.
	 */
	public
		$pSocket = null,
		$pMessage = null,
		$pConfig = null,

		$pEventHandlers = null,
		$pCurrentScript = null,
		$pUsers = null,
		$pChannels = null;


	private
		$aScripts = array(),
		$aSockets = array(),
		$iNetworksLoaded = false,

		$pBotItter = null;


	/**
	 *	Called when the instance is created.
	 */
	public function __construct($pConfig)
	{
		$this->pConfig = $pConfig;

		$this->pMessage = new stdClass();
		$this->pChannels = new stdClass();
		$this->pUsers = new stdClass();
		$this->pEventHandlers = new stdClass();

		$this->pBotItter = (object) array
		(
			"iIndex" => 0,
			"iPosition" => 0,
		);
	}


	/**
	 *	Called when any other undefined method is called.
	 */
	public function __call($sMethod, $aArguments)
	{
		try
		{
			$sMethod = strtolower($sMethod);

			if(!isset(Core::$pFunctionList->$sMethod))
			{
				return null;
			}

			$cStaticMethod = Core::$pFunctionList->$sMethod;

			$pReflection = new ReflectionMethod($cStaticMethod[0], $cStaticMethod[1]);
			return $pReflection->invokeArgs(null, $aArguments);
		}
		catch(ReflectionException $pError)
		{
			return null;
		}
	}


	/**
	 *	Called when the class is invoked.
	 */
	public function __invoke($sMessage, $mFlags)
	{
		return $this->Raw($sMessage, $mFlags);
	}


	/**
	 *	Called when the bot is ready to be run.
	 */
	public function initiateInstance()
	{
		$pNetwork = $this->pConfig->Network;

		foreach($pNetwork->scriptArray as $sScriptName)
		{
			$this->activateScript($sScriptName);
		}

		foreach($this->pConfig->Bots as $pBot)
		{
			$pBot->handle = $pBot->nickname;
			$pBot->host = $pNetwork->host;
			$pBot->port = $pNetwork->port;

			$this->aSockets[] = new CoreSocket($this, $pBot);

			println(" - Loaded {$pNetwork->name}/{$pBot->handle}");

			++$this->pBotItter->iCount;
		}

		$this->iNetworksLoaded = true;
	}


	/**
	 *	Creates a new Socket instance.
	 */
	public function addChild($sNickname, array $aOptions = array())
	{
		if(!isset($aOptions['username']))
		{
			$aOptions['username'] = strtolower($sNickname);
		}

		if(!isset($aOptions['realname']))
		{
			$aOptions['realname'] = $sNickname;
		}

		if(!isset($aOptions['nickname']))
		{
			$aOptions['nickname'] = $sNickname;
		}

		$aOptions['handle'] = $sNickname;
		$aOptions['slave'] = true;

		$aOptions['host'] = $this->pConfig->Network->host;
		$aOptions['port'] = $this->pConfig->Network->port;

		$this->aSockets[] = new CoreSocket($this, (object) $aOptions);
		++$this->pBotItter->iCount;

		println(" - Loaded {$this->pConfig->Network->name}/{$aOptions['handle']}");
		return $aOptions['handle'];
	}


	/**
	 *	Returns a list of Sockets.
	 */
	public function getListOfChildren()
	{
		$aReturn = array();

		foreach($this->aSockets as $pSocket)
		{
			$aReturn[$pSocket->pConfig->handle] = $pSocket->pConfig->nickname;
		}

		return $aReturn;
	}


	/**
	 *	Removes a Socket instance.
	 */
	public function removeChild($sHandle, $sReason = null)
	{
		foreach($this->aSockets as $pSocket)
		{
			if($pSocket->pConfig->handle != $sHandle)
			{
				continue;
			}

			if(!$pSocket->pConfig->slave)
			{
				return false;
			}

			$pSocket->destroyConnection($sReason);
			unset($pSocket);

			--$this->pBotItter->iCount;
			$this->aSockets = array_values($this->aSockets);
		}
	}


	/**
	 *	Function to scan through all the sockets.
	 */
	public function Socket()
	{
		foreach($this->aSockets as $pSocket)
		{
			$pSocket->Socket();
		}
	}


	/**
	 *	This function gets the next child along in the queue.
	 */
	public function getNextSocket()
	{
		if($this->pBotItter->iIndex >= $this->pBotItter->iCount)
		{
			$this->pBotItter->iIndex = 0;
		}

		$pBot = $this->aSockets[$this->pBotItter->iIndex];
		++$this->pBotItter->iIndex;

		return $pBot;
	}


	/**
	 *	Returns the current in use socket.
	 */
	public function getCurrentSocket()
	{
		return $this->pSocket;
	}


	/**
	 *	Returns a list of all callable functions in OUTRAGEbot.
	 *	You have got to love Reflection.
	 */
	public function getCallableMethods()
	{
		$pClass = new ReflectionClass(__CLASS__);
		$aMethods = array();

		foreach($pClass->getMethods() as $pMethod)
		{
			$aMethods[] = $pMethod->name;
		}

		foreach(array_keys(Core::$pFunctionList) as $sFunctionName)
		{
			$aMethods[] = $sFunctionName;
		}

		return sort($aMethods, SORT_STRING);
	}


	/**
	 *	This function returns the network configuration.
	 */
	public function getNetworkConfiguration($sConfigKey = null)
	{
		if(!isset($this->pConfig->Network->$sConfigKey))
		{
			return null;
		}

		return $sConfigKey == null ? $this->pConfig->Network : $this->pConfig->Network->$sConfigKey;
	}


	/**
	 *	This function returns the network configuration.
	 */
	public function getServerConfiguration($sConfigKey = null)
	{
		if(!isset($this->pConfig->Server->$sConfigKey))
		{
			return null;
		}

		return $sConfigKey == null ? $this->pConfig->Server : $this->pConfig->Server->$sConfigKey;
	}


	/**
	 *	The public entry point for all inbound socket communication.
	 */
	public function Portkey(CoreSocket $pSocket, $sString)
	{
		$pMessage = $this->internalPortkey($pSocket, $sString);

		if($pMessage === null)
		{
			return false;
		}

		if($pSocket->isSocketSlave())
		{
			return CoreHandler::Unhandled($this, $pMessage);
		}

		return Core::Handler($this, $pMessage);
	}


	/**
	 *	This fake-private method deals with the construction of the portkey.
	 */
	public function internalPortkey(CoreSocket $pSocket, $sString)
	{
		$pMessage = Core::getMessageObject($sString);

		$this->pMessage = $pMessage;
		$this->pSocket = $pSocket;

		if($pMessage->Parts[0] == "PING")
		{
			$pSocket->Output("PONG ".$pMessage->Parts[1]);
			return null;
		}

		return $pMessage;
	}


	/**
	 *	Send stuff to the outside world.
	 */
	public function Raw($sRawString, $mFlags = SEND_DEF)
	{
		if(is_array($sRawString))
		{
			foreach($sRawString as $sString)
			{
				$this->Raw($sString, $mFlags);
			}

			return;
		}

		# The message modifications
		if($mFlags & FORMAT)
		{
			$sRawString = Format::parseInputString($sRawString);
		}


		# The outbound channels
		if($mFlags & SEND_DEF)
		{
			$mFlags = $this->pConfig->Network->rotation;
		}

		if($mFlags & SEND_MAST)
		{
			return $this->aSockets[0]->Output($sRawString);
		}

		elseif($mFlags & SEND_CURR)
		{
			return $this->pSocket->Output($sRawString);
		}

		elseif($mFlags & SEND_ALL)
		{
			foreach($this->aSockets as $pSocket)
			{
				$pSocket->Output($sRawString);
			}

			return;
		}

		return $this->getNextSocket()->Output($sRawString);
	}


	/**
	 *	Sends a message to the specified channel.
	 */
	public function Message($sChannel, $sMessage, $mFlags = SEND_DEF)
	{
		return $this->Raw("PRIVMSG {$sChannel} :{$sMessage}", $mFlags);
	}


	/**
	 *	Sends an action to the specified channel.
	 */
	public function Action($sChannel, $sMessage, $mFlags = SEND_DEF)
	{
		return $this->Raw("PRIVMSG {$sChannel} :".chr(1)."ACTION {$sMessage}".chr(1), $mFlags);
	}


	/**
	 *	Sends a notice to the specified channel.
	 */
	public function Notice($sNickname, $sMessage, $mFlags = SEND_DEF)
	{
		return $this->Raw("NOTICE {$sNickname} :{$sMessage}", $mFlags);
	}


	/**
	 *	Checks if that user has voice in that channel. Voicers have the
	 *	mode ' + '.
	 */
	public function isUserVoice($sChannel, $sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		$pChannel = $this->getChannel($sChannel);

		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qaohv]/', $pChannel->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has half-op in that channel. Half operators
	 *	have the mode ' % ', and may not be available on all networks.
	 */
	public function isUserHalfOp($sChannel, $sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		$pChannel = $this->getChannel($sChannel);

		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qaoh]/', $pChannel->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has operator in that channel. Operators have
	 *	the mode ' @ '.
	 */
	public function isUserOp($sChannel, $sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		$pChannel = $this->getChannel($sChannel);

		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qao]/', $pChannel->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has admin in that channel. Admins have the
	 *	mode ' & ', and may not be available on all networks.
	 */
	public function isUserAdmin($sChannel, $sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		$pChannel = $this->getChannel($sChannel);

		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qa]/', $pChannel->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has owner in that channel. Owners have the
	 *	mode ' ~ ', and may not be available on all networks.
	 */
	public function isUserOwner($sChannel, $sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		$pChannel = $this->getChannel($sChannel);

		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[q]/', $pChannel->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user is in the channel.
	 *	Why this was removed, I have zero idea.
	 */
	public function isUserInChannel($sChannel, $sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		$pChannel = $this->getChannel($sChannel);

		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}

		return true;
	}


	/**
	 *	Check if the current, active IRC user is a bot admin.
	 */
	public function isAdmin()
	{
		return in_array($this->pMessage->User->Hostname, $this->pConfig->Network->ownerArray) !== false;
	}


	/**
	 *	Get the users username from a hostname string.
	 */
	public function getUsername($sHostname)
	{
		return self::parseHostmask($sHostname)->Username;
	}


	/**
	 *	Get the users nickname from a hostname string.
	 */
	public function getNickname($sHostname)
	{
		return self::parseHostmask($sHostname)->Nickname;
	}


	/**
	 *	Get the users hostname from a hostname string.
	 */
	public function getHostname($sHostname)
	{
		return self::parseHostmask($sHostname)->Hostname;
	}


	/**
	 *	Get the hostmask info as an array.
	 */
	public static function parseHostmask($sHostname)
	{
		$bMatch = preg_match('/(.*)!(.*)@(.*)/', $sHostname, $aDetails);

		if($bMatch)
		{
			return (object) array
			(
				"Nickname" => $aDetails[1],
				"Username" => $aDetails[2],
				"Hostname" => $aDetails[3],
			);
		}
		else
		{
			return (object) array
			(
				"Nickname" => $sHostname,
				"Username" => $sHostname,
				"Hostname" => $sHostname,
			);
		}
	}


	/**
	 *	Activate a Script from the Script directory.
	 */
	public function activateScript($sScriptName)
	{
		$sIdentifier = CoreUtilities::getScriptIdentifier($sScriptName);

		if($sIdentifier == false)
		{
			return false;
		}

		println(" * Activated script '{$sScriptName}'");

		$this->aScripts[$sScriptName] = new $sIdentifier($this, $sScriptName);

		if($this->iNetworksLoaded)
		{
			$this->triggerEvent("onConnect");
		}

		return true;
	}


	/**
	 *	Remove a Script from the local instance.
	 */
	public function deactivateScript($sScriptName)
	{
		if(empty($this->aScripts[$sScriptName]))
		{
			return false;
		}

		foreach($this->aScripts[$sScriptName]->getLocalEventHandlers() as $sHandlerID)
		{
			$this->removeEventHandler($sHandlerID);
		}

		foreach($this->aScripts[$sScriptName]->getLocalTimerHandlers() as $sTimerID)
		{
			$this->removeTimer($sTimerID);
		}

		$this->aScripts[$sScriptName]->onDestruct();
		$this->aScripts[$sScriptName]->prepareRemoval();

		println(" * Deactivated script '{$sScriptName}'");
		unset($this->aScripts[$sScriptName]);

		return true;
	}


	/**
	 *	Reactivate a Script, used when there's an update for instance.
	 */
	public function reactivateScript($sScriptName)
	{
		$this->deactivateScript($sScriptName);
		return $this->activateScript($sScriptName);
	}


	/**
	 *	Return a list of activated Scripts.
	 */
	public function getActivatedScripts()
	{
		return array_keys($this->aScripts);
	}


	/**
	 *	Add an event handler into the local instance.
	 */
	public function addEventHandler($sEventName, $cCallback, $sArgumentFormat = null, $mArguments = null)
	{
		if(!is_callable($cCallback))
		{
			$cCallback = array($this->pCurrentScript, $cCallback);

			if(!is_callable($cCallback))
			{
				return false;
			}
		}

		$sHandlerID = uniqid("vce");
		$iEventType = 0;

		$this->pCurrentScript->addLocalEventHandler($sHandlerID);

		if(substr($sEventName, 0, 2) == "on")
		{
			$sEventName = "+{$sEventName}";
			$iEventType = EVENT_HANDLER;
		}
		else
		{
			$sEventName = strtoupper($sEventName);

			if($sArgumentFormat == null)
			{
				$iEventType = EVENT_INPUT;
			}
			else
			{
				$iEventType = EVENT_CUSTOM;
			}
		}

		$this->pEventHandlers->{$sEventName}[$sHandlerID] = (object) array
		(
			"eventID" => $sHandlerID,
			"eventType" => $iEventType,
			"eventCallback" => $cCallback,
			"argumentTypes" => $sArgumentFormat,
			"argumentPassed" => $mArguments,
		);

		return $sHandlerID;
	}


	/**
	 *	Add a command handler into the local instance.
	 *	I'm cheating with this, aren't I?
	 */
	public function addCommandHandler($sCommandName, $cCallback)
	{
		if(!is_callable($cCallback))
		{
			$cCallback = array($this->pCurrentScript, $cCallback);

			if(!is_callable($cCallback))
			{
				return false;
			}
		}

		$sHandlerID = uniqid("vcc");

		$this->pCurrentScript->addLocalEventHandler($sHandlerID);

		$this->pEventHandlers->PRIVMSG[$sHandlerID] = (object) array
		(
			"eventID" => $sHandlerID,
			"eventType" => EVENT_COMMAND,
			"eventCallback" => $cCallback,
			"argumentTypes" => null,
			"argumentPassed" => $sCommandName,
		);

		return $sHandlerID;
	}


	/**
	 *	Remove an event handler from the local instance.
	 */
	public function removeEventHandler($sHandlerID)
	{
		foreach($this->pEventHandlers as &$pEvent)
		{
			foreach(array_keys($pEvent) as $sHandlerGID)
			{
				if($sHandlerID == $sHandlerGID)
				{
					unset($pEvent[$sHandlerID]);
				}
			}

			if(count($pEvent) == 0)
			{
				unset($pEvent);
			}
		}
	}


	/**
	 *	Trigger an event for loaded Scripts and handlers.
	 *	Like everything else, needs cleaning up.
	 */
	public function triggerEvent()
	{
		$aArguments = func_get_args();
		$sEventName = array_shift($aArguments);

		# Go through the handlers - they have high presidence than Scripts.
		if(isset($this->pEventHandlers->{"+{$sEventName}"}))
		{
			$aEventHandlers = $this->pEventHandlers->{"+{$sEventName}"};

			foreach($aEventHandlers as $pEventHandler)
			{
				$iReturn = Core::invokeReflection($pEventHandler->eventCallback, $aArguments);

				if(Core::assert($iReturn))
				{
					return;
				}
			}
		}

		# And finally, the scripts.
		foreach($this->aScripts as $pScriptInstance)
		{
			$pInstance = array
			(
				$pScriptInstance,
				$sEventName
			);

			$iReturn = Core::invokeReflection($pInstance, $aArguments);

			if(Core::assert($iReturn))
			{
				return;
			}
		}

		return;
	}


	/**
	 *	Retrieves a user or a channel object.
	 */
	public function getObject($sObject)
	{
		$sPattern = "/[".preg_quote($this->getServerConfiguration("CHANTYPES"))."]/";

		if(preg_match($sPattern, $sObject[0]))
		{
			return $this->getChannel($sObject);
		}

		return $this->getUser($sObject);
	}


	/**
	 *	Retrieve the user object.
	 */
	public function getUser($sNickname)
	{
		if(is_object($sNickname))
		{
			if($sNickname instanceof CoreUser)
			{
				return $sNickname;
			}

			elseif(isset($sNickname->Nickname))
			{
				$pUser = $this->getUser($sNickname->Nickname);
				$pUser->pHostmaskObject = $sNickname;

				return $pUser;
			}

			return null;
		}

		if(!isset($this->pUsers->$sNickname))
		{
			$this->pUsers->$sNickname = new CoreUser($this, $sNickname);
		}

		return $this->pUsers->$sNickname;
	}


	/**
	 *	Retrieve the channel object.
	 */
	public function getChannel($sChannel)
	{
		if($sChannel instanceof CoreChannel)
		{
			return $sChannel;
		}

		$sChannel = strtolower($sChannel);

		if(!isset($this->pChannels->$sChannel))
		{
			$this->pChannels->$sChannel = new CoreChannel($this, $sChannel);
		}

		return $this->pChannels->$sChannel;
	}


	/**
	 *	Adds formatting to the text.
	 */
	public function Format($sInputText)
	{
		return Format::parseInputString($sInputText);
	}


	/**
	 *	Strips the text of formatting.
	 */
	public function stripFormat($sText)
	{
		return preg_replace("/[\002\017\001\026\001\037]/", "", $sText);
	}


	/**
	 *	Strips the text of colours.
	 */
	public function stripColour($sText)
	{
		return preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", "", $sText);
	}


	/**
	 *	Strips the text of formatting and colours.
	 */
	public function stripAll($sText)
	{
		return preg_replace("/[\002\017\001\026\001\037]/", "", preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", "", $sText));
	}


	/**
	 *	Makes the bot join a channel.
	 */
	public function Join($sChannel, $mFlags = SEND_DEF)
	{
		return $this->Raw("JOIN {$sChannel}", $mFlags);
	}


	/**
	 *	Makes the bot leave a channel.
	 */
	public function Part($sChannel, $sReason = null, $mFlags = SEND_DEF)
	{
		$sPart = "PART {$sChannel}";

		if($sReason != null)
		{
			$sPart .= " :{$sReason}";
		}

		return $this->Raw($sPart, $mFlags);
	}


	/**
	 *	Invite a user to the channel
	 */
	public function Invite($sNickname, $sChannel)
	{
		return $this->Raw("INVITE {$sNickname} {$sChannel}");
	}
}
