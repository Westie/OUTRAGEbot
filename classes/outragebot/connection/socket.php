<?php
/**
 *	Socket class for OUTRAGEbot
 */


namespace OUTRAGEbot\Connection;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;
use \OUTRAGEbot\Element;
use \OUTRAGEbot\Module;


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
	public $configuration = null;
	
	
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
	 *	This stores the ping timer index.
	 */
	private $pingtimer = null;
	
	
	/**
	 *	Stores the total number of failed pings. A failed ping is a ping that hasn't
	 *	been responded to within I'd say, ten or so seconds?
	 */
	private $pingindex = 0;
	
	
	/**
	 *	What is the nickname of this bot?
	 */
	public $nickname = null;
	
	
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
		$this->socket = null;
		$this->statistics = null;
		$this->prepared = false;
		$this->listener = null;
		$this->backlog = [];
		$this->pingtimer = null;
		$this->pingindex = 0;
		
		$settings = array
		(
			"socket" => [],
		);
		
		if($this->configuration->bind)
			$settings["socket"]["bindto"] = $this->configuration->bind;
		
		$this->nickname = $this->configuration->nick;
		
		$errno = null;
		$errstr = null;
		
		$this->socket = stream_socket_client("tcp://".$this->configuration->host.":".$this->configuration->port, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, stream_context_create($settings));
		
		$this->block(false);
		$this->handshake();
		
		# this rather awkward bit is here to grab the timer method - since we
		# could in theory run this bot w/o a timer, we need to treat the ping
		# timer as an optional extra.
		if($closure = Module\Stack::getInstance()->getClosure("setInterval"))
		{
			$context = new Element\Context();
			$context->callee = $this;
			$context->instance = null;
			
			$this->pingtimer = $closure($context, [ $this, "sendPingMessage" ], 180);
		}
		
		return $this;
	}
	
	
	/**
	 *	Disconnect from the server.
	 */
	public function disconnect($reason = "")
	{
		$this->write("QUIT :".$reason);
		
		fclose($this->socket);
		
		if($this->pingtimer)
		{
			if($closure = Module\Stack::getInstance()->getClosure("removeTimer"))
			{
				$context = new Element\Context();
				$context->callee = $this;
				$context->instance = null;
				
				$closure($context, $this->pingtimer);
			}
		}
		
		$this->socket = null;
		$this->statistics = null;
		$this->prepared = false;
		$this->listener = null;
		$this->backlog = [];
		$this->pingtimer = null;
		$this->pingindex = 0;
		
		return $this;
	}
	
	
	/**
	 *	Run the handshake.
	 */
	public function handshake()
	{
		if($this->prepared)
			return false;
		
		if($this->configuration->password)
			$this->write("PASS ".$this->configuration->password);
		
		$this->write("NICK ".$this->nickname);
		$this->write("USER ".$this->configuration->username." x x :".$this->configuration->realname);
		
		return true;
	}
	
	
	/**
	 *	Called from some event handler when the socket is ready to have things actually done
	 *	to it, like join channels and such.
	 */
	public function ready()
	{
		# Run perform[] commands from configuration file
		if(!empty($this->parent->network->perform))
		{
			foreach($this->parent->network->perform as $command)
				$this->write($command);
		}

		# Join channels specified in configuration file
		if(!empty($this->parent->network->channels))
		{
			foreach($this->parent->network->channels as $channel)
				$this->write("JOIN ".$channel);
		}
		
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
		if($cache)
		{
			if(count($this->backlog))
				return array_shift($this->backlog);
		}
		
		if(!$this->socket)
			return false;
		
		$ticks = 0;
		$buffer = "";
		
		# ouch - forgive me, CPU
		while(true)
		{
			$character = fgetc($this->socket);
			
			if($character === false)
			{
				# interesting scenario - if there's no buffer then we can immediately
				# leave - otherwise, sleep for a small amount of time and see if this
				# will help resolve the situation
				if(!strlen($buffer))
					return false;
				
				if($ticks > 100)
					break;
				
				++$ticks;
				
				usleep(100);
				continue;
			}
			
			$ticks = 0;
			
			if($character == "\r")
			{
				$next = fgetc($this->socket);
				
				if($next == "\n")
					break;
				
				$buffer .= $character;
				$character = $next;
			}
			elseif($character == "\r" || $character == "\n")
			{
				break;
			}
			
			$buffer .= $character;
		}
		
		if(!strlen($buffer))
			return false;
		
		$packet = new Packet($this->parent, $buffer);
		
		if($this->listener)
			$this->backlog[] = $packet;
		
		# we need to check to see if there's a PONG response in here - we could
		# write this to be in the PONG response handler but considering how it is
		# being sent out here it would make sense if it was handled here too
		if($packet->numeric == "PONG")
			$this->pingindex = 0;
		
		return $packet;
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
	
	
	/**
	 *	This sends a ping message to the server. Too many of these and
	 *	we'll disconnect and start again.
	 */
	public function sendPingMessage()
	{
		++$this->pingindex;
		
		if($this->pingindex >= 3)
		{
			$this->disconnect("Ping timeout");
			$this->connect();
		}
		else
		{
			$this->write("PING ".time());
		}
		
		return true;
	}
	
	
	/**
	 *	Set the bot's nickname.
	 */
	public function setNickname($nickname)
	{
		if(!$nickname)
			return false;
		
		$this->nickname = $nickname;
		
		return true;
	}
}
