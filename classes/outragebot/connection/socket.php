<?php
/**
 *	Socket class for OUTRAGEbot
 */


namespace OUTRAGEbot\Connection;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;


class Socket
{
	/**
	 *	We'll include our delegation trait, just to keep things
	 *	simple.
	 */
	use Attributes\Delegator;
	
	
	/**
	 *	We'll need to keep track of the parent instance.
	 */
	private $parent = null;
	
	
	/**
	 *	We'll also need to provide a localised copy of the configuration,
	 *	specific to this bot only.
	 */
	private $configuration = null;
	
	
	/**
	 *	We also'll need to keep a track of the socket.
	 */
	private $socket = null;
	
	
	/**
	 *	We can also track, for ego purposes, how long the socket has been open for,
	 *	how many bytes it has transmitted and recieved.
	 */
	private $statistics = null;
	
	
	/**
	 *	In a departure from previous versions, we will store the server configuration in
	 *	each socket instance. The instance class can just steal it from here and populate
	 *	it there.
	 */
	public $serverconf = null;
	
	
	/**
	 *	Another smart idea is dealing with prepping the connection - like, joining channels
	 *	and such - in here, this flag will allow us to check if the socket has already
	 *	been prepared or not.
	 */
	public $prepared = false;
	
	
	/**
	 *	If there is a synchronous socket listener in use, it will appear here.
	 */
	private $listener = null;
	
	
	/**
	 *	This provides a location where packets can be stored whilst a listener is blocking the bot.
	 */
	private $backlog = [];
	
	
	/**
	 *	Called whenever an intention to have a socket has been raised.
	 */
	public function __construct(Instance $parent)
	{
		$this->parent = $parent;
		$this->serverconf = [];
	}
	
	
	/**
	 *	Called to configure the socket.
	 */
	public function configure(Core\Configuration $configuration)
	{
		$this->configuration = $configuration;
		
		return $this;
	}
	
	
	/**
	 *	Creates the socket and connects to the network.
	 */
	public function connect()
	{
		$settings = array
		(
			"socket" => [],
		);
		
		if($this->configuration->bind)
			$settings["socket"]["bindto"] = $this->configuration->bind;
		
		$errno = null;
		$errstr = null;
		
		$this->socket = stream_socket_client("tcp://".$this->configuration->host.":".$this->configuration->port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, stream_context_create($settings));
		$this->block(false);
		
		if($this->configuration->password)
			$this->write("PASS ".$this->configuration->password);
		
		$this->write("NICK ".$this->configuration->nick);
		$this->write("USER ".$this->configuration->username." x x :".$this->configuration->realname);
		
		return $this;
	}
	
	
	/**
	 *	Called from some event handler when the socket is ready to have things actually done
	 *	to it, like join channels and such.
	 */
	public function ready()
	{
		foreach($this->parent->network->channels as $channel)
			$this->write("JOIN ".$channel);
		
		return $this->prepared = true;
	}
	
	
	/**
	 *	Sets the blocking status of this socket.
	 */
	public function block($status = true)
	{
		if($this->socket)
			stream_set_blocking($this->socket, (boolean) $status);
		
		return $this;
	}
	
	
	/**
	 *	Writes a message to the socket.
	 */
	public function write($message)
	{
		if($this->socket)
			fwrite($this->socket, $message."\r\n");
		
		return $this;
	}
	
	
	/**
	 *	Returns any input from the socket, if there is any.
	 */
	public function read($cache = true)
	{
		if($this->socket)
		{
			if($cache)
			{
				if(count($this->backlog))
					return array_shift($this->backlog);
			}
			
			$result = fgets($this->socket, 1024); # should be 512 max, need to detect missing \n ending or something
			$result = trim($result);
			
			if(!$result)
				return false;
			
			$packet = new Packet($this->parent, $result);
			
			if($this->listener)
				$this->backlog[] = $packet;
			
			return $packet;
		}
		
		return false;
	}
	
	
	/**
	 *	Disconnects this socket from the network and resets all pointers.
	 */
	public function disconnect()
	{
		if($this->socket)
		{
			fclose($this->socket);
			
			$this->socket = null;
		}
		
		return $this;
	}
	
	
	/**
	 *	Checks if there is a valid socket connection to the server.
	 */
	public function getter_connected()
	{
		if(!$this->socket)
			return false;
		
		return true;
	}
	
	
	/**
	 *	Set the status of the current socket listener.
	 */
	public function setSocketListener(SocketListener $listener)
	{
		$this->listener = $listener;
		return $this;
	}
	
	
	/**
	 *	Removes the socket listener.
	 */
	public function clearSocketListener()
	{
		$this->listener = null;
		return $this;
	}
}