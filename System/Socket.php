<?php
/**
 *	Socket class for OUTRAGEbot
 *
 *	I suppose I have no intention to document this piece of code,
 *	I suppose.
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0.0
 */

class Socket
{
	public
		$oMaster = null,
		$aConfig = array(),
		$aStatistics = array(),
		$rSocket = null,
		
		$isWaiting = false,
		$iPingTimer = -1,
		$iHasBeenReply = false,
		
		$iNoReply = 0,
		$isActive = false,
		$isRemove = false,
		
		$iUseQueue = false,
		$sChild = "",
		$aSearch = array(),
		$aMsgQueue = array(),
		$aMatchQueue = array();
	
	
	/* Constructing the class */
	public function constructBot()
	{
		/* Resetting statistics */
		$this->aStatistics = array
		(
			"StartTime" => time(),
			"Input" => array
			(
				"Packets" => 0,
				"Bytes" => 0,
			),
			"Output" => array
			(
				"Packets" => 0,
				"Bytes" => 0,
			),
		);
		
		/* Shortcut, eh? */
		$oConfig = $this->oMaster->oConfig;
		
		$this->rSocket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		if(isset($oConfig->Network['bind']))
		{
			socket_bind($this->rSocket, $oConfig->Network['bind']);
		}
		
		socket_connect($this->rSocket, $oConfig->Network['host'], $oConfig->Network['port']);
		if(!isset($_SERVER['WINDIR']) && !isset($_SERVER['windir']))
		{
			socket_set_nonblock($this->rSocket);
		}
		
		if(isset($this->aConfig['password']))
		{
			$this->Output("PASS {$this->aConfig['password']}");
		}
		
		$this->Output("NICK {$this->aConfig['nickname']}");
		$this->Output("USER {$this->aConfig['username']} x x :{$this->aConfig['username']}");
		
		$this->Active = true;
		$this->isWaiting = false;
		$this->iPingTimer = Timers::Create(array($this, "Ping"), 60, -1);
	}
	
	
	/* The socket gets shutdown by unsetting the class. */
	public function destructBot($sMessage = false)
	{
		$this->Output('QUIT :'.($sMessage == false ? $this->oMaster->oConfig->Network['quitmsg'] : $sMessage));
		Timers::Delete($this->iPingTimer);
		
		@socket_clear_error();
		
		$this->socketShutdown();
		$this->Active = false;
		$this->isWaiting = false;
	}
	
	
	/* The real constructor. */
	public function __construct($oMaster, $sChild, $aBasic)
	{
		$this->aConfig = $aBasic;
		$this->oMaster = $oMaster;
		$this->sChild = $sChild;
		
		$this->constructBot();
	}
	
	
	/* The real destructor. */
	public function __destruct()
	{
	}
	
	
	/* Sending data from socket */
	public function Output($sRaw)
	{
		++$this->aStatistics['Output']['Packets'];
		$this->aStatistics['Output']['Bytes'] += strlen($sRaw.IRC_EOL);
		return @socket_write($this->rSocket, $sRaw.IRC_EOL);
	}
	
	
	/* All important ping check */
	public function Ping()
	{
		if(!$this->iHasBeenReply)
		{
			$this->iNoReply++;
			$this->iHasBeenReply = false;
		}
		
		if($this->iNoReply >= 5)
		{
			$this->destructBot();
			Timers::Create(array($this, "constructBot"), $this->aConfig['timewait'], 0);
			
			$this->isWaiting = true;
			$this->iNoReply = 0;
		}			
		
		$this->Output("PING ".time());
		$this->iHasBeenReply = false;
	}

	
	/* Recieving data from socket */
	public function Input()
	{	
		if(!$this->isSocketActive())
		{
			if(!$this->isWaiting)
			{
				$this->destructBot();
				$this->isWaiting = true;
				$this->constructBot();
			}
			return true;
		}	
		
		if($this->isWaiting) return true;
		
		if(count($this->aMsgQueue) && $this->iUseQueue == false)
		{
			foreach($this->aMsgQueue as $iKey => &$sChunk)
			{
				$this->oMaster->getSend($this, $sChunk);
				unset($this->aMsgQueue[$iKey]);
			}
		}
				
		if(($sInput = socket_read($this->rSocket, 4096, PHP_BINARY_READ)))
		{
			$aInput = explode(IRC_EOL, $sInput);
			foreach($aInput as $sChunk)
			{	
				++$this->aStatistics['Input']['Packets'];
				$this->aStatistics['Input']['Bytes'] += strlen($sChunk);
				$this->oMaster->getSend($this, $sChunk);
			}
		}
		
		return true;
	}
	
	
	/* Is socket online? */
	private function isSocketActive()
	{
		if(!$this->Active)
		{
			return false;
		}
		
		if(!is_resource($this->rSocket))
		{
			return false;
		}
		
		if(strlen(socket_last_error($this->rSocket)) > 3)
		{
			return false;
		}
		
		return true;
	}
	
	
	/* Making it easier to check of clones */
	public function isClone()
	{
		return ($this->aConfig['slave'] !== false);
	}
	
	
	/* Changing the bot's nickname! */
	public function setNickname($sNickname)
	{
		$this->aConfig['nickname'] = $sNickname;
		$this->Output("NICK {$sNickname}");
	}
	
	
	/* Shutting down socket - used for restarting, and dying. */
	public function socketShutdown()
	{
		return @socket_shutdown($this->rSocket);
	}
}

?>
