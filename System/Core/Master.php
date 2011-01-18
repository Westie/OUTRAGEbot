<?php
/**
 *	OUTRAGEbot development
 */


class CoreMaster
{
	public
		$pSocket = null,
		$pMessage = null,
		$pConfig = null,
		
		$pEventHandlers = null,
		$pCurrentScript = null,
		$pChannels = null;
	
	
	private
		$aScripts = array(),
		$aSockets = array(),
		
		$pBotItter = null;
	
	
	/**
	 *	Called when the network is loaded.
	 */
	public function __construct($pConfig)
	{
		$this->pConfig = $pConfig;
		
		$this->pMessage = new stdClass();
		$this->pChannels = new stdClass();
		$this->pEventHandlers = new stdClass();
		
		$this->pBotItter = (object) array
		(
			"iIndex" => 0,
			"iPosition" => 0,
		);
		
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
		
		return $aMethods;
	}
	
	
	/**
	 *	This function returns the network configuration.
	 */
	public function getNetworkConfiguration($sConfigKey = null)
	{
		return $sConfigKey == null ? $this->pConfig->Network : $this->pConfig->Network->$sConfigKey;
	}
	
	
	/**
	 *	This function returns the network configuration.
	 */
	public function getServerConfiguration($sConfigKey = null)
	{
		return $sConfigKey == null ? $this->pConfig->Server : $this->pConfig->Server->$sConfigKey;
	}
	
	
	/**
	 *	This function returns the current socket's configuration.
	 */
	public function getSocketConfiguration($sConfigKey = null)
	{
		return $sConfigKey == null ? $this->pConfig->Socket : $this->pConfig->Socket->$sConfigKey;
	}
	
	
	/**
	 *	This function to deal with the input data.
	 */
	public function Portkey(CoreSocket $pSocket, $sString)
	{
		$pMessage = Core::getMessageObject($sString);
		
		if($pMessage->Parts[0] == "PING")
		{
			return $pSocket->Output("PONG ".$pMessage->Parts[1]);
		}
		
		$this->pMessage = $pMessage;
		$this->pSocket = $pSocket;
		
		if($pSocket->isSocketSlave())
		{
			return CoreHandler::Unhandled($this, $pMessage);
		}
		
		return Core::Handler($this, $pMessage);
	}
	
	
	/**
	 *	Send stuff to the outside world.
	 */
	public function Raw($sRawString, $mOption = SEND_DEF)
	{
		if($mOption == SEND_DEF)
		{
			$mOption = $this->pConfig->Network->rotation;
		}
		
		switch($mOption)
		{
			case SEND_MAST:
			{
				return $this->aSockets[0]->Output($sRawString);
			}
			
			case SEND_CURR:
			{
				return $this->pSocket->Output($sRawString);
			}
			
			case SEND_ALL:
			{
				foreach($this->aSockets as $pSocket)
				{
					$pSocket->Output($sRawString);
				}
				
				return;
			}
			
			case SEND_DIST:
			default:
			{
				return $this->getNextChild()->Output($sRawString);
			}
		}
	}
	
	
	/**
	 *	Sends a message to the specified channel.
	 */
	public function Message($sChannel, $sMessage, $mOption = SEND_DEF)
	{
		return $this->Raw("PRIVMSG {$sChannel} :{$sMessage}", $mOption);
	}
	
	
	/**
	 *	Sends an action to the specified channel.
	 */
	public function Action($sChannel, $sMessage, $mOption = SEND_DEF)
	{
		return $this->Raw("PRIVMSG {$sChannel} :".chr(1)."ACTION {$sMessage}".chr(1), $mOption);
	}
	
	
	/**
	 *	Sends a notice to the specified channel.
	 */
	public function Notice($sNickname, $sMessage, $mOption = SEND_DEF)
	{
		return $this->Raw("NOTICE {$sNickname} :{$sMessage}", $mOption);
	}
	
	
	/**
	 *	Sends a CTCP reply.
	 */
	public function ctcpReply($sNickname, $sMessage)
	{
		return $this->Raw("NOTICE {$sNickname} :".chr(1).trim($sMessage).chr(1), SEND_CURR);
	}
	
	
	/**
	 *	Sends a CTCP request.
	 *	Not fully implemented yet.
	 */
	public function ctcpRequest($sNickname, $sRequest)
	{
		return $this->Raw("PRIVMSG {$sNickname} :".chr(1).trim($sRequest).chr(1), SEND_CURR);
	}
	
	
	/**
	 *	Checks if that user has voice in that channel. Voicers have the
	 *	mode ' + '.
	 */
	public function isUserVoice($sChannel, $sUser)
	{
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
		$pChannel = $this->getChannel($sChannel);
		
		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}
		
		return preg_match('/[q]/', $pChannel->pUsers->$sUser) == true;
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
	static function parseHostmask($sHostname)
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
		
		foreach($this->aScripts[$sScriptName]->aHandlerCache as $sHandlerID)
		{
			$this->removeEventHandler($sHandlerID);
		}

		$this->aScripts[$sScriptName]->onDestruct();
		
		println(" * Deactivated script '{$sScriptName}'");
		
		unset($this->aScripts[$sScriptName]->aHandlerCache);
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
		return array_keys($this->aScripts[$sScriptName]);
	}
	
	
	/**
	 *	Add an event handler into the local instance.
	 */
	public function addEventHandler($sEventName, $cCallback, $sArgumentFormat = null)
	{
		if(!is_callable($cCallback))
		{
			$cCallback = array($this->pCurrentScript, $cCallback);
		}
		
		$sHandlerID = uniqid("nat");
		$sEventName = strtoupper($sEventName);
		
		$this->pCurrentScript->aHandlerCache[] = $sHandlerID;
		$this->pEventHandlers->{$sEventName}[$sHandlerID] = array($cCallback, $sArgumentFormat);
		
		return $sHandlerID;
	}
	
	
	/**
	 *	Add a command handler into the local instance.
	 *	I'm cheating with this, aren't I?
	 */
	public function addCommandHandler($sCommandName, $cCallback)
	{		
		return $this->addEventHandler('PRIVMSG', $cCallback, chr(0xFF).$sCommandName);
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
	 *	Trigger an event for loaded Scripts.
	 */
	public function triggerEvent()
	{
		$aArguments = func_get_args();
		$sEventName = array_shift($aArguments);
		
		foreach($this->aScripts as $pScriptInstance)
		{
			$mReturn = null;
			
			if(method_exists($pScriptInstance, $sEventName))
			{
				$mReturn = call_user_func_array(array($pScriptInstance, $sEventName), $aArguments);
			}
			
			if($mReturn == END_EVENT_EXEC)
			{
				return;
			}
		}
		
		return;
	}
	
	
	/**
	 *	Retrieve the channel object.
	 */
	public function getChannel($sChannel)
	{
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
		return Format($sInputText);
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
		return preg_replace("/[\002\017\001\026\001\037]/", "", 
		preg_replace("/\003[0-9]{1,2}(,[0-9]{1,2})?/", "", $sText));
	}
	
	
	/**
	 *	Makes the bot join a channel.
	 */
	public function Join($sChannel, $mOption = SEND_DEF)
	{
		return $this->Raw("JOIN {$sChannel}", $mOption);
	}
	
	
	/**
	 *	Makes the bot leave a channel.
	 */
	public function Part($sChannel, $sReason = null, $mOption = SEND_DEF)
	{
		$sPart = "PART {$sChannel}";
		
		if($sReason != null)
		{
			$sPart .= " :{$sReason}";
		}
		
		return $this->Raw($sPart, $mOption);
	}
	
	
	/**
	 *	Invite a user to the channel
	 */
	public function Invite($sNickname, $sChannel)
	{
		return $this->Raw("INVITE {$sNickname} {$sChannel}");
	}
}
