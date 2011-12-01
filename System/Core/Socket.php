<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     4a7dced0b3ef96338f36bc64bd40ed91063c3e01
 *	Committed at:   Thu Dec  1 22:49:57 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreSocket extends CoreChild
{
	private
		$cSocketCallback = false,
		$aCaptureStack = array(),
		$sTimerID = false,
		$rSocket = false;


	public
		$iPingTime = 0,
		$iPingMiss = 0,
		$pConfig = false;


	/**
	 *	Called when the class is created.
	 */
	public function __construct($pMaster, $pConfig)
	{
		$this->pConfig = $pConfig;
		$this->pConfig->Capture = false;

		$this->internalMasterObject($pMaster);

		$this->resetSocketHandler();
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

		if(!isset($this->pConfig->altnick))
		{
			$this->pConfig->altnick = $this->pConfig->nickname;
		}

		$rSocketOptions = stream_context_create($aSocketOptions);

		$this->rSocket = @stream_socket_client("tcp://{$this->pConfig->host}:{$this->pConfig->port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $rSocketOptions);

		if($this->rSocket === false)
		{
			CoreTimer::Add(array($this, "createConnection"), 60, 1);
			return;
		}

		$this->setSocketBlocking(false);

		if(isset($this->pConfig->password))
		{
			$this->Output("PASS {$this->pConfig->password}");
		}

		$this->Output("NICK {$this->pConfig->nickname}");
		$this->Output("USER {$this->pConfig->username} x x :{$this->pConfig->realname}");

		$this->pConfig->StartTime = time();

		$this->sTimerID = CoreTimer::Add(array($this, "pingServer"), 120, -1);

		return;
	}


	/**
	 *	Close the connection to the IRC network.
	 */
	public function destroyConnection($sReason = null)
	{
		$this->internalMasterObject()->triggerEvent("onDisconnect");

		$this->Output('QUIT :'.($sReason == null ? $this->internalMasterObject()->getNetworkConfiguration("quitmsg") : $sReason));
		fclose($this->rSocket);

		CoreTimer::Remove($this->sTimerID);

		$this->rSocket = false;
		$this->sTimerID = false;
		$this->iPingMiss = false;

		return;
	}


	/**
	 *	Checks that the bot is connected to the IRC network.
	 */
	public function pingServer()
	{
		if($this->iPingMiss === true)
		{
			$this->destroyConnection();
			CoreTimer::Add(array($this, "createConnection"), 3);

			return;
		}

		$this->iPingTime = time();
		$this->iPingMiss = true;

		$this->Output("PING {$this->iPingTime}");
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

			if(!$this->pConfig->Capture)
			{
				call_user_func($this->getSocketHandler(), $this, $sString);
			}
			else
			{
				$this->aCaptureStack[] = $sString;
			}
		}

		return;
	}


	/**
	 *	Sets the socket handler.
	 *	Only for advanced module operations.
	 */
	public function setSocketHandler($cCallback)
	{
		$this->cSocketCallback = $cCallback;
	}


	/**
	 *	Retrieves the socket handler.
	 *	Only for advanced module operations.
	 */
	public function getSocketHandler()
	{
		return $this->cSocketCallback;
	}


	/**
	 *	Sets the socket handler.
	 *	Only for advanced module operations.
	 */
	public function resetSocketHandler()
	{
		$this->cSocketCallback = array($this->internalMasterObject(), "Portkey");
	}


	/**
	 *	Toggle the blocking of sockets.
	 *	Only for advanced module operations.
	 */
	public function setSocketBlocking($bBlocking)
	{
		return stream_set_blocking($this->rSocket, ($bBlocking ? 1 : 0));
	}


	/**
	 *	Begin the capturing of incoming data.
	 */
	public function startCapture()
	{
		$this->pConfig->Capture = true;
		$this->setSocketBlocking(true);
	}


	/**
	 *	Get the captured packets.
	 */
	public function getCapture()
	{
		if(!count($this->aCaptureStack))
		{
			usleep(BOT_TICKRATE);

			$this->Socket();

			return $this->getCapture();
		}

		return array_shift($this->aCaptureStack);
	}


	/**
	 *	An alternative, cleaner way of implementing the capture device.
	 */
	public function executeCapture($cCallback)
	{
		$this->startCapture();

		while(true)
		{
			$mReturn = call_user_func($cCallback, $this->getCapture());

			if($mReturn === true)
			{
				break;
			}
		}

		$this->stopCapture();
	}


	/**
	 *	Stop the capturing of incoming data.
	 */
	public function stopCapture()
	{
		$this->pConfig->Capture = false;
		$this->setSocketBlocking(false);

		foreach($this->aCaptureStack as $sString)
		{
			call_user_func($this->getSocketHandler(), $this, $sString);
		}

		$this->aCaptureStack = array();
	}


	/**
	 *	Set the socket's nickname
	 */
	public function setSocketNickname($sNewNickname)
	{
		$this->pConfig->nickname = $sNewNickname;
		$this->Output($sNewNickname);
	}
}
