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
		$pCurrentScript = null;
	
	
	private
		$aScripts = array(),
		$aSockets = array(),
		
		$pChannels = null,
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
	 *	This function returns the network configuration.
	 */
	public function getNetworkConfiguration()
	{
		return $this->pConfig->Network;
	}
	
	
	/**
	 *	This function returns the network configuration.
	 */
	public function getServerConfiguration()
	{
		return $this->pConfig->Server;
	}
	
	
	/**
	 *	This function returns the current socket's configuration.
	 */
	public function getSocketConfiguration()
	{
		return $this->pSocket->pConfig;
	}
	
	
	/**
	 *	This function to deal with the input data.
	 */
	public function Portkey(CoreSocket $pSocket, $sString)
	{
		$pMessage = new stdClass();
		
		$pMessage->Raw = $sString;
		$pMessage->Parts = explode(' ', $sString);
		$pMessage->User = $this->parseHostmask(substr($pMessage->Parts[0], 1));
		$pMessage->Numeric = $pMessage->Parts[1];
		$pMessage->Payload = (($iPosition = strpos($sString, ':', 2)) !== false) ? substr($sString, $iPosition + 1) : '';
		
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
		
		return preg_match('/[qaohv]/', $pChannel->pUsers->$sUser);
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
		
		return preg_match('/[qaoh]/', $pChannel->pUsers->$sUser);
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
		
		return preg_match('/[qao]/', $pChannel->pUsers->$sUser);
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
		
		return preg_match('/[qa]/', $pChannel->pUsers->$sUser);
	}
	
	
	/**
	 *	Checks if that user has owner in that channel. Owners have the
	 *	mode ' ~ ', and may not be available on all networks.
	 *
	 *	@param string $sChannel Channel where user is
	 *	@param string $sUser Nickname to check
	 *	@return bool 'true' on success.
	 */
	public function isUserOwner($sChannel, $sUser)
	{	
		$pChannel = $this->getChannel($sChannel);
		
		if(!isset($pChannel->pUsers->$sUser))
		{
			return false;
		}
		
		return preg_match('/[q]/', $pChannel->pUsers->$sUser);
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
		return $this->parseHostmask($sHostname)->Username;
	}
	
	
	/**
	 *	Get the users nickname from a hostname string.
	 */
	public function getNickname($sHostname)
	{
		return $this->parseHostmask($sHostname)->Nickname;
	}
	
	
	/**
	 *	Get the users hostname from a hostname string.
	 */
	public function getHostname($sHostname)
	{
		return $this->parseHostmask($sHostname)->Hostname;
	}
	
	
	/**
	 *	Get the hostmask info as an array.
	 */
	public function parseHostmask($sHostname)
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
				"Nickname" => null,
				"Username" => null,
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
		
		$this->aScripts[$sScriptName] = new $sIdentifier($this);
		return true;
	}
	
	
	/**
	 *	Remove a Script from the local instance.
	 */
	public function deactivateScript($sScriptName)
	{
		unset($this->aScripts[$sScriptName]);
	}
	
	
	/**
	 *	Add an event handler into the local instance.
	 */
	public function addEventHandler($sEventName, $cCallback)
	{
		if(!is_callable($cCallback))
		{
			$cCallback = array($this->pCurrentScript, $cCallback);
		}
		
		$sHandlerID = uniqid("ehn");
		$sEventName = strtoupper($sEventName);
		
		if(empty($this->pEventHandlers->$sEventName))
		{
			$this->pEventHandlers->$sEventName = array();
		}
		
		$this->pEventHandlers->$sEventName[$sHandlerID] = $cCallback;
		
		
		
		return $sHandlerID;
	}
	
	
	/**
	 *	Remove an event handler from the local instance.
	 */
	public function removeEventHandler($sHandlerID)
	{
		foreach($this->pEventHandlers as $pEvent)
		{
			foreach(array_keys($pEvent) as $sHandlerGID)
			{
				if($sHandlerID == $sHandlerGID)
				{
					unset($pEvent[$sHandlerID]);
				}
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
			$mReturn = call_user_func_array(array($pScriptInstance, $sEventName), $aArguments);
			
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
}