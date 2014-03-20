<?php
/**
 *	Commands script for OUTRAGEbot.
 */


class commands9538469f63413f8972d91e527a86908ae4c2a85c extends OUTRAGEbot\Script\Instance
{
	/**
	 *	Stores all the commands currently in use.
	 */
	public $commands = [];
	
	
	/**
	 *	Called whenever the script is constructed.
	 */
	public function construct()
	{
		$this->addCommandHandler("acommand", "add");
		$this->addCommandHandler("dcommand", "remove");
		
		$this->on("PRIVMSG", "execute");
		
		$files = $this->resourceScan("*.object");
		
		if($files)
		{
			foreach($files as $item)
				$this->import($item);
		}
		
		return true;
	}
	
	
	/**
	 *	Called when adding a command handler to this system.
	 */
	public function add($channel, $user, $payload)
	{
		if(!$user->is_admin)
			return true;
		
		$params = explode(" ", $payload, 2);
		
		if(count($params) != 2)
		{
			$user->notice("acommand: [command] [instructions in PHP]");
			return true;
		}
		
		$set = array
		(
			"command" => $params[0],
			"instructions" => $params[1],
		);
		
		$file = sha1($params[0]).".object";
		
		$this->resourcePut($file, serialize($set));
		$this->import($file);
		
		$user->notice("acommand: successfully added ".$params[0]);
		return true;
	}
	
	
	/**
	 *	Called when removing a command handler.
	 */
	public function remove($channel, $user, $payload)
	{
		if(!$user->is_admin)
			return true;
		
		$payload = trim($payload);
		
		if(!$payload)
		{
			$user->notice("dcommand: [command]");
			return true;
		}
		
		if(!isset($this->commands[$payload]))
		{
			$user->notice("dcommand: command doesn't exist");
			return true;
		}
		
		unset($this->commands[$payload]);
		
		$this->resourceUnlink(sha1($payload).".object");
		
		$user->notice("dcommand: successfully removed ".$payload);
		return true;
	}
	
	
	/**
	 *	Called whenever there is a message of some sort in the channel.
	 */
	public function execute($event)
	{
		$key = substr($event->packet->parts[3], 1);
		
		if(!isset($this->commands[$key]))
			return false;
		
		$channel = $this->instance->getChannel($event->packet->parts[2]);
		$user = $this->instance->getUser($event->packet->user);
		$message = substr($event->packet->payload, strlen($key) + 1);
		
		ob_start();
		
		$this->commands[$key]($channel, $user, $message);
		
		$output = ob_get_flush();
		
		if($output)
			$channel->send($output);
		
		return true;
	}
	
	
	/**
	 *	Import a file into memory.
	 */
	protected function import($file)
	{
		$object = $this->resourceGet($file);
		
		if(!$object)
			return false;
		
		$object = unserialize($object);
		$callback = null;
		
		$template = "";
		
		$template .= '$callback = function($channel, $user, $message) {'.PHP_EOL;
		$template .= ' /* automatically generated */ ';
		$template .= $object["instructions"].PHP_EOL;
		$template .= '}; ';
		
		eval($template);
		
		$this->commands[$object["command"]] = $callback;
		return true;
	}
}
