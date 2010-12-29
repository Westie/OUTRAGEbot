<?php
/**
 *	OUTRAGEbot development
 */


class CoreMaster
{
	public
		$pSocket = null,
		$pMessage = null,
		$pConfig = null;
	
	
	private
		$aPlugins = array(),
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
		
		$this->pBotItter = (object) array
		(
			"iIndex" => 0,
			"iPosition" => 0,
		);
		
		$pNetwork = $this->pConfig->Network;
		
		foreach($this->pConfig->Bots as $pBot)
		{
			$pBot->handle = $pBot->nickname;
			$pBot->host = $pNetwork->host;
			$pBot->port = $pNetwork->port;
			
			$this->aSockets[] = new CoreSocket($this, $pBot);
			
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
				return $this->aSockets[0]->Output($sMessage);
			}
			case SEND_CURR:
			{
				return $this->pSocket->Output($sMessage);
			}
			case SEND_ALL:
			{
				foreach($this->aSockets as $pSocket)
				{
					$pSocket->Output($sMessage);
				}
				
				return;
			}
			case SEND_DIST:
			default:
			{
				return $this->getNextChild()->Output($sMessage);
			}
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
	 *	Function to deal with the input data.
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
	 *	Activate a plugin from the plugin directory.
	 */
	public function activatePlugin($sPluginName)
	{
		$sIdentifier = getPluginIdentifier($sPluginName);
		
		if($sIdentifier == false)
		{
			return false;
		}
		
		$this->aPlugins[$sPluginName] = new $sIdentifier();		
		return true;
	}
	
	
	/**
	 *	Trigger an event for loaded plugins.
	 */
	public function triggerEvent()
	{
		$aArguments = func_get_args();
		$sEventName = array_shift($aArguments);
		
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
}