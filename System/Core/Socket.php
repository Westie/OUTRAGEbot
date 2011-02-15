<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     e75544e55f1917e98a40c6eabfd2a530262ab803
 *	Committed at:   Tue Feb 15 22:05:13 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreSocket
{
	private
		$aCaptureStack = array(),
		$rSocket = null,
		$pMaster = null,
		$pSocketHandler = null;


	public
		$pConfig = null;


	/**
	 *	Called when the class is created.
	 */
	public function __construct($pMaster, $pConfig)
	{
		$this->pMaster = $pMaster;
		$this->pConfig = $pConfig;

		$this->pConfig->Capture = false;

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

		$rSocketOptions = stream_context_create($aSocketOptions);

		$this->rSocket = stream_socket_client("tcp://{$this->pConfig->host}:{$this->pConfig->port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $rSocketOptions);
		$this->setSocketBlocking(false);

		if(isset($this->pConfig->password))
		{
			$this->Output("PASS {$this->pConfig->password}");
		}

		$this->Output("NICK {$this->pConfig->nickname}");
		$this->Output("USER {$this->pConfig->username} x x :{$this->pConfig->realname}");

		$this->pConfig->StartTime = time();

		return;
	}


	/**
	 *	Close the connection to the IRC network.
	 */
	public function destroyConnection($sReason)
	{
		$this->Output('QUIT :'.($sReason == null ? $this->pMaster->pConfig->Network->quitmsg : $sReason));
		fclose($this->rSocket);

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

			# I wish for an easier solution, but this wish, like
			# so many that I have, will never be realised.

			if(!$this->pConfig->Capture)
			{
				call_user_func($this->cSocketHandler, $this, $sString);
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
		$this->cSocketHandler = $cCallback;
	}


	/**
	 *	Retrieves the socket handler.
	 *	Only for advanced module operations.
	 */
	public function getSocketHandler()
	{
		return $this->cSocketHandler;
	}


	/**
	 *	Sets the socket handler.
	 *	Only for advanced module operations.
	 */
	public function resetSocketHandler()
	{
		$this->cSocketHandler = array($this->pMaster, "Portkey");
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
			call_user_func($this->cSocketHandler, $this, $sString);
		}
	}
}
