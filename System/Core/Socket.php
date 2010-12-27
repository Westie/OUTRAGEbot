<?php
/**
 *	OUTRAGEbot development
 */


class CoreSocket
{
	private
		$rSocket = null,
		$pMaster = null,
		$pConfig = null;
	
	
	/**
	 *	Called when the class is created.
	 */
	public function __construct($pMaster, $pConfig)
	{
		$this->pMaster = $pMaster;
		$this->pConfig = $pConfig;
		
		$this->createConnection();
	}
	
	
	/**
	 *	Create the connection to the IRC network.
	 */
	public function createConnection()
	{
		$aSocketOptions = array();
		
		if(isset($this->pConfig->bindto))
		{
			$aSocketOptions['socket']['bindto'] = $this->pConfig->bindto;
		}
		
		$rSocketOptions = stream_context_create($aSocketOptions);
		
		$this->rSocket = stream_socket_client("tcp://{$this->pConfig->host}:{$this->pConfig->port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $rSocketOptions);
		$this->setSocketBlocking(false);
		
		if(isset($this->pConfig->password))
		{
			$this->Output("PASS {$this->pConfig->password}");
		}
		
		$this->Output("NICK {$this->pConfig->nickname}");
		$this->Output("USER {$this->pConfig->username} x x :{$this->pConfig->realname}");
		
		return;
	}
	
	
	/**
	 *	Close the connection to the IRC network.
	 */
	public function closeConnection()
	{
		return;
	}

	
	/**
	 *	Deal with outbound packets.
	 */
	public function Output($sString)
	{
		return fputs($this->rSocket, $sString."\r\n");
	}
	
	
	/**
	 *	Is the socket a bot?
	 */
	public function isSocketSlave()
	{
		return $this->pConfig->slave != false;
	}
	
	
	/**
	 *	Check if the socket is active.
	 */
	public function isSocketActive()
	{
		return is_resource($this->rSocket);
	}
	
	
	/**
	 *	Deal with incoming packets.
	 */
	public function Socket()
	{
		if(!$this->isSocketActive())
		{
			return;
		}
		
		$sInputString = fgets($this->rSocket, 4096);
		
		foreach(explode("\r\n", $sInputString) as $sString)
		{		
			if(strlen($sString) < 3)
			{
				continue;
			}
			
			$this->pMaster->Portkey($this, $sString);
		}
		
		return;
	}
	
	
	/**
	 *	Toggle the blocking of sockets.
	 */
	public function setSocketBlocking($bBlocking)
	{
		return stream_set_blocking($this->rSocket, ($bBlocking ? 1 : 0));
	}
}