<?php
/**
 *	WHOIS module for OUTRAG3bot.
 */


namespace OUTRAGEbot\Module\Modules;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Module;
use \OUTRAGEbot\Connection;


class Whois extends Module\Template
{
	/**
	 *	Store the WHOIS response for this request.
	 */
	private $response = null;
	
	
	/**
	 *	Called whenever the module has been loaded into memory.
	 */
	public function construct()
	{
		$this->introduceMethod([ "whois", "getWhois" ], "retrieve");
	}
	
	
	/**
	 *	Hook for the introduced method.
	 */
	public function retrieve($context, $nickname)
	{
		$arguments = array
		(
			"retrieve" => array
			(
				"301" => [ $this, "parseInputAwayMessage" ],
				"307" => [ $this, "parseInputRegistered" ],
				"310" => [ $this, "parseInputHelperMessage" ],
				"311" => [ $this, "parseInputUser" ],
				"312" => [ $this, "parseInputServer" ],
				"313" => [ $this, "parseInputIRCOp" ],
				"317" => [ $this, "parseInputConnectionTime" ],
				"319" => [ $this, "parseInputChannels" ],
				"378" => [ $this, "parseInputSourceIP" ],
				"379" => [ $this, "parseInputConnectionModes" ],
				"671" => [ $this, "parseInputSecureConnection" ],
			),
			
			"success" => array
			(
				"318" => [ $this, "parseInputSuccess" ],
			),
		);
		
		$this->response = $this->populateEmptyResponse();
		
		$socket = $context->instance->getCurrentSocket();
		$socket->write("WHOIS ".$nickname." ".$nickname);
		
		$listener = new Connection\SocketListener($socket, $arguments);
		
		if(!$listener)
			return null;
		
		return $listener->run();
	}
	
	
	/**
	 *	WHOIS Response: Away message
	 */
	public function parseInputAwayMessage(Connection\Packet $packet)
	{
		$this->response->away = $packet->payload;
	}


	/**
	 *	WHOIS Response: Nick registration status
	 */
	public function parseInputRegistered(Connection\Packet $packet)
	{
		$this->response->registered = true;
	}
	
	
	/**
	 *	WHOIS Response: Helper boolean
	 */
	public function parseInputHelperMessage(Connection\Packet $packet)
	{
		$this->response->helper = true;
	}
	
	
	/**
	 *	WHOIS Response: User info
	 */
	public function parseInputUser(Connection\Packet $packet)
	{
		$this->response->nickname = $packet->parts[3];
		$this->response->username = $packet->parts[4];
		$this->response->address = $packet->parts[5];
		$this->response->realname = $packet->payload;
	}
	
	
	/**
	 *	WHOIS Response: Server info
	 */
	public function parseInputServer(Connection\Packet $packet)
	{
		$this->response->serverAddress = $packet->parts[4];
		$this->response->serverName = $packet->payload;
	}
	
	
	/**
	 *	WHOIS Response: IRCOp boolean
	 */
	public function parseInputIRCOp(Connection\Packet $packet)
	{
		$this->response->ircOp = true;
	}
	
	
	/**
	 *	WHOIS Response: Connection time
	 */
	public function parseInputConnectionTime(Connection\Packet $packet)
	{
		$this->response->idleTime = $packet->parts[4];
		$this->response->signonTime = $packet->parts[5];
	}
	
	
	/**
	 *	WHOIS Response: Channels
	 */
	public function parseInputChannels(Connection\Packet $packet)
	{
		$this->response->channels = array_merge($this->response->channels, explode(" ", $packet->payload));
	}
	
	
	/**
	 *	WHOIS Response: Source IP
	 */
	public function parseInputSourceIP(Connection\Packet $packet)
	{
		$matches = array();
		
		if(preg_match("/^is connecting from (.*?) (.*?)$/", $packet->payload, $matches))
			$this->response->ipAddress = $matches[2];
	}
	
	
	/**
	 *	WHOIS Response: Connection modes
	 */
	public function parseInputConnectionModes(Connection\Packet $packet)
	{
		$matches = array();
		
		if(preg_match("/^is using modes (.*?) (.*?)$/", $packet->payload, $matches))
		{
			$this->response->userModes = $matches[1];
			$this->response->serverModes = $matches[2];
		}
	}
	
	
	/**
	 *	WHOIS Response: Secure mode
	 */
	public function parseInputSecureConnection(Connection\Packet $packet)
	{
		$this->response->isSecure = true;
	}
	
	
	/**
	 *	Called at the end of a successful WHOIS request.
	 */
	public function parseInputSuccess(Connection\Packet $packet)
	{
		return $this->response;
	}
	
	
	/**
	 *	Creates an empty response array for use with this object.
	 */
	protected function populateEmptyResponse()
	{
		$set = array
		(
			"address" => null,
			"away" => null,
			"channels" => [],
			"helper" => null,
			"idleTime" => 0,
			"ircOp" => null,
			"nickname" => null,
			"realname" => null,
			"serverAddress" => null,
			"serverName" => null,
			"signonTime" => 0,
			"username" => null,
			"isSecure" => false,
			"ipAddress" => null,
			"userModes" => null,
			"serverModes" => null,
			"registered" => false
		);
		
		$response = new Core\ObjectContainer();
		$response->populateContainer($set);
		
		return $response;
	}
}