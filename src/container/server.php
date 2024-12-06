<?php
/**
 *	IPC container server for OUTRAG3bot.
 */


namespace OUTRAGEbot\Container;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;


class Server
{
	/**
	 *	We'll need the Singleton functionality here.
	 */
	use Attributes\Singleton;
	
	
	/**
	 *	Assign a variable for our IPC socket here.
	 */
	private $socket = null;
	
	
	/**
	 *	Oh, and also assign the hive. Might be useful.
	 */
	private $hive = null;
	
	
	/**
	 *	Called when the server has been constructed.
	 */
	public function __construct($hive)
	{
		$this->hive = $hive;
	}
	
	
	/**
	 *	Start all listenings on this server!
	 */
	public function connect()
	{
		$this->socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
		
		socket_bind($this->socket, "resources/run/socket");
		socket_listen($this->socket);
		socket_set_nonblock($this->socket);
	}
	
	
	/**
	 *	Called when the hive has been destructed.
	 */
	public function disconnect()
	{
		socket_shutdown($this->socket);
		socket_close($this->socket);
	}
	
	
	/**
	 *	Writes a message to the socket.
	 */
	public function write($message)
	{
		if($this->socket)
		{
			$token = $message."\r\n";
			$length = strlen($token);
			
			socket_send($this->socket, $token, $length, MSG_EOR);
		}
		
		return $this;
	}
	
	
	/**
	 *	Returns any input from the socket, if there is any.
	 */
	public function read()
	{
		
		return false;
	}
}