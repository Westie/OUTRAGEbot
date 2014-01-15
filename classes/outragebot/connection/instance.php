<?php
/**
 *	Instance class for OUTRAGEbot
 */


namespace OUTRAGEbot\Connection;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;
use \OUTRAGEbot\Element;
use \OUTRAGEbot\Event;
use \OUTRAGEbot\Module;
use \OUTRAGEbot\Script;


class Instance
{
	/**
	 *	We need to have a delegator here.
	 */
	use Attributes\Delegator;
	
	
	/**
	 *	Store our configuration here.
	 */
	private $configuration = null;
	
	
	/**
	 *	Store some socket references here...
	 */
	private $sockets = null;
	
	
	/**
	 *	Store all our users in here.
	 */
	public $users = null;
	
	
	/**
	 *	Store all our channels in here.
	 */
	public $channels = null;
	
	
	/**
	 *	Store all our scripts in here.
	 */
	public $scripts = null;
	
	
	/**
	 *	Store a shadow of all event listeners in here.
	 */
	public $events = null;
	
	
	/**
	 *	Returns an instance of the event delegator.
	 */
	public function getter_delegator()
	{
		return $this->delegator = Event\Delegator::getInstance();
	}
	
	
	/**
	 *	Returns the server's configuration, parsed into easy to understand sets.
	 */
	public function getter_serverconf()
	{
		$configuration = new Core\ObjectContainer();
		$socket = $this->sockets[0];
		
		if($socket->serverconf)
		{
			# mapping user mode prefixes
			$configuration["prefixes"] = array();
			
			if(isset($socket->serverconf["PREFIX"]))
			{
				$matches = array();
				
				preg_match("/^\((.*)\)(.*)$/", $socket->serverconf["PREFIX"], $matches);
				
				$chars = preg_split("//", $matches[1], -1, PREG_SPLIT_NO_EMPTY);
				$symbols = preg_split("//", $matches[2], -1, PREG_SPLIT_NO_EMPTY);
				
				foreach($chars as $index => $value)
					$configuration["prefixes"][$value] = $symbols[$index];
			}
			
			# mapping channel prefixes
			$configuration["chantypes"] = array();
			
			if(isset($socket->serverconf["CHANTYPES"]))
				$configuration["chantypes"] = preg_split("//", $socket->serverconf["CHANTYPES"], -1, PREG_SPLIT_NO_EMPTY);
			
			# mapping channel modes
			$configuration["chanmodes"] = array();
			
			if(isset($socket->serverconf["CHANMODES"]))
			{
				$modes = explode(",", $socket->serverconf["CHANMODES"]);
				
				foreach($modes as $mode)
					$configuration["chanmodes"][] = preg_split("//", $mode, -1, PREG_SPLIT_NO_EMPTY);
			}
		}
		
		return $configuration;
	}
	
	
	/**
	 *	Retrieves the network configuration.
	 */
	public function getter_network()
	{
		return $this->configuration->network;
	}
	
	
	/**
	 *	Called whenever an instance gets created.
	 */
	public function __construct()
	{
		foreach([ "channels", "users" ] as $item)
			$this->__set($item, new Element\Structure());
		
		foreach([ "events", "scripts", "sockets" ] as $item)
			$this->__set($item, new Core\ObjectContainer());
		
		return true;
	}
	
	
	/**
	 *	Configure this instance with some configurations.
	 */
	public function configure(Core\Configuration $configuration)
	{
		$this->configuration = $configuration;
		
		# first, we'll need to create some bots
		foreach($this->configuration->bots as $bot)
		{
			$socket = new Socket($this);
			$children = new Core\Configuration();
			
			$properties = [ "identifier", "host", "port" ];
			
			foreach($properties as $property)
				$children[$property] = $this->configuration->network[$property];
			
			foreach($bot as $property => $item)
				$children[$property] = $item;
			
			$this->sockets->push($socket->configure($children));
		}
		
		# then we'll need to load some scripts
		if($this->configuration->network->scripts)
		{
			foreach($this->configuration->network->scripts as $script)
				$this->activateScript($script);
		}
		
		Module\Stack::getInstance()->trigger("instanceInit", [ $this ]);
		
		return $this;
	}
	
	
	/**
	 *	Return all sockets that this instance has.
	 */
	public function sockets()
	{
		return $this->sockets;
	}
	
	
	/**
	 *	Return all users that this instance has.
	 */
	public function users()
	{
		return $this->users;
	}
	
	
	/**
	 *	Connect all of the sockets to the network.
	 */
	public function connect()
	{
		foreach($this->sockets as $socket)
			$socket->connect();
		
		return $this;
	}
	
	
	/**
	 *	Polls connections and does stuff with them.
	 */
	public function poll()
	{
		foreach($this->sockets as $socket)
		{
			while($packet = $socket->read())
				$this->delegator->getEvent($packet)->newInstanceArgs([ $this, $socket, $packet ])->invoke();
		}
		
		return $this;
	}
	
	
	/**
	 *	Disconnects all sockets in this instance.
	 */
	public function disconnect()
	{
		foreach($this->sockets as $socket)
			$socket->disconnect();
		
		return $this;
	}
	
	
	/**
	 *	Retrieves a user from the internal user cache.
	 */
	public function getUser($pattern)
	{
		if($pattern instanceof Element\User)
			return $pattern;
		
		if($pattern instanceof Hostmask)
			$hostmask = $pattern;
		else
			$hostmask = new Hostmask($this, $pattern);
		
		if(!isset($this->users[$hostmask->nickname]))
			$this->users[$hostmask->nickname] = new Element\User($this, $hostmask);
		
		return $this->users[$hostmask->nickname];
	}
	
	
	/**
	 *	Retrieves a channel from the internal channel cache.
	 */
	public function getChannel($channel)
	{
		if($channel instanceof Element\Channel)
			return $channel;
		
		if(!isset($this->channels[$channel]))
			$this->channels[$channel] = new Element\Channel($this, $channel);
		
		return $this->channels[$channel];
	}
	
	
	/**
	 *	Retrieves the current socket object.
	 */
	public function getCurrentSocket()
	{
		$count = count($this->sockets);
		
		if($count == 1)
			return $this->sockets->first();
		
		return null;
	}
	
	
	/**
	 *	Retrieves the current socket that is in use, 
	 */
	public function getter_socket()
	{
		$count = count($this->sockets);
		
		if($count == 1)
			return $this->sockets->first();
		
		return null;
	}
	
	
	/**
	 *	Send a raw string to the server using the first socket that
	 *	is available.
	 */
	public function raw($string)
	{
		return $this->socket->write($string);
	}
	
	
	/**
	 *	Activates a script.
	 */
	public function activateScript($script)
	{
		$this->scripts->push(Script\Intrepreter::getInstance()->compile($this, $script));
		return $this;
	}
}