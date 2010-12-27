<?php
/**
 *	OUTRAGEbot development
 */


class CoreMaster
{
	private
		$pMessage = null,
		$pConfig = null;
	
	
	private		
		$aPlugins = array(),
		$aSockets = array();
	
	
	/**
	 *	Called when the network is loaded.
	 */
	public function __construct($pConfig)
	{
		$this->pConfig = $pConfig;
		$this->pMessage = new stdClass();
		
		$pNetwork = $this->pConfig->Network;
		
		foreach($this->pConfig->Bots as $pBot)
		{
			$pBot->handle = $pBot->nickname;
			$pBot->host = $pNetwork->host;
			$pBot->port = $pNetwork->port;
			
			$this->aSockets[] = new CoreSocket($this, $pBot);
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
			$pSocket->Output("PONG ".$pMessage->Parts[1]);
			return;
		}
		
		$this->pMessage = $pMessage;
		
		if($pSocket->isSocketSlave())
		{
			$this->_onRaw($pMessage);
			return;
		}
		
		
		Core::Handler($this, $pMessage);
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
	 *	Trigger an event for loaded plugins
	 */
	public function triggerEvent()
	{
		$aArguments = func_get_args();
		
		return;
	}
}