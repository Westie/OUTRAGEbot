<?php
/**
 *	The script intrepreter for OUTRAG3bot - deals with intrepreting
 *	scripts so that scripts can be reloaded multiple times.
 *
 *	Obviously, this particular class might become obselete whenever
 *	I get the multi-process bot working, but for now, this will help.
 */


namespace OUTRAGEbot\Script;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Core\Attributes;
use \OUTRAGEbot\Element;
use \OUTRAGEbot\Event;
use \OUTRAGEbot\Module;


class Instance
{
	/**
	 *	Include our delegator code
	 */
	use Attributes\Delegator;
	
	
	/**
	 *	Creates a context reference that is passed to modules and such.
	 */
	private $context = null;
	
	
	/**
	 *	Defines the parent instance of this script.
	 */
	protected $instance = null;
	
	
	/**
	 *	Stores the actual name of the script.
	 *	Used for resources or something.
	 */
	public $_real_script_name = null;
	
	
	/**
	 *	Called when the script has been loaded.
	 */
	public final function __construct($script, $instance)
	{
		$this->_real_script_name = $script;
		
		$this->context = new Element\Context();
		$this->context->callee = $this;
		$this->context->instance = $instance;
		
		$this->instance = $instance;
		
		return $this->construct();
	}
	
	
	/**
	 *	Called when the script has been removed.
	 */
	public final function __destruct()
	{
		$this->destruct();
		$this->off();
		
		unset($this->context);
		unset($this->instance);
		
		return true;
	}
	
	
	/**
	 *	Also called when the script has been loaded, however this thing you can use.
	 */
	public function construct()
	{
		return true;
	}
	
	
	/**
	 *	Called when the script has been unloaded.
	 */
	public function destruct()
	{
		return true;
	}
	
	
	/**
	 *	Retrieves an instance of reflector for this class.
	 */
	public function getter_reflection()
	{
		return $this->reflection = new \ReflectionObject($this);
	}
	
	
	/**
	 *	Called to bind an event handler to this script.
	 *	
	 *	@param string $event      Event name
	 *	@param callback $handler  Callback
	 *	
	 *	@example input documentation/examples/script-on/input1.txt
	 *	@example input documentation/examples/script-on/input2.txt
	 */
	public function on($event, $handler, $metadata = [])
	{
		$matches = [];
		
		preg_match("/^(.*?)(\.(.*?))?$/", $event, $matches);
		
		if($matches)
			$type = (string) $matches[1];
		
		if(!is_numeric($type))
		{
			if(preg_match("/^on(.*)$/", $type))
				$type = substr($type, 2);
		}
		
		$type = strtolower($type);
		
		if(!isset($this->instance->events[$type]))
			$this->instance->events[$type] = new Core\ObjectContainer();
		
		$this->instance->events[$type]->push(new Event\Handler($this, $event, $handler, $metadata));
		
		return $this;
	}
	
	
	/**
	 *	Called to remove event handlers that belong to this script.
	 *	
	 *	@param mixed $event  Event name
	 */
	public function off($event = null)
	{
		if(!empty($event))
		{
			$matches = [];
			
			preg_match("/^(.*?)(\.(.*?))?$/", $event, $matches);
			
			if($matches)
				$type = (string) $matches[1];
		
			if(!is_numeric($type))
			{
				if(preg_match("/^on(.*)$/", $type))
					$type = substr($type, 2);
			}
			
			$type = strtolower($type);
			
			if(!empty($this->instance->events[$type]))
			{
				foreach($this->instance->events[$type] as $key => $item)
				{
					if($item->getContext() == $this)
						unset($this->instance->events[$type][$key]);
				}
			}
		}
		else
		{
			foreach($this->instance->events as $type => $struct)
			{
				foreach($struct as $key => $item)
				{
					if($item->getContext() == $this)
						unset($this->instance->events[$type][$key]);
				}
			}
		}
		
		return $this;
	}
	
	
	/**
	 *	Called to bind a command handler to this script.
	 *
	 *	@param string $command    Command to listen to
	 *	@param callback $handler  Callback for this command handler.
	 */
	public function addCommandHandler($command, $handler)
	{
		$is_closure = $handler instanceof \Closure;
		
		if(!$is_closure)
		{
			if(method_exists($this, $handler))
			{
				$handler = $this->reflection->getMethod($handler)->getClosure($this);
			}
			elseif(is_array($handler))
			{
				$reflection = new \ReflectionObject($handler[0]);
				
				if($reflection->hasMethod($handler[1]))
					$handler = $reflection->getMethod($handler[1])->getClosure($handler[0]);
			}
			else
			{
				$handler = (new \ReflectionFunction($handler))->getClosure();
			}
		}
		
		if(!$handler)
			return false;
		
		$closure = function($c, $u, $x, $p) use ($command, $handler)
		{
			if($command == $x)
				return $handler($c, $u, $p);
			
			return false;
		};
		
		return $this->on("channelcommand", $closure, [ "__command_handler" => $command ]);
	}
	
	
	/**
	 *	Removes a command handler that is assigned to this script.
	 *
	 *	@param string $command  Command to be removed.
	 *
	 *	@todo: Perhaps there's a simpler way to do this rather than basically rewriting the
	 *	       capabilities of Instance::off?
	 */
	public function removeCommandHandler($command)
	{
		if(empty($this->instance->events["channelcommand"]))
			return $this;
		
		foreach($this->instance->events["channelcommand"] as $key => $item)
		{
			if(empty($item->metadata))
				continue;
			
			if($item->getContext() == $this)
			{
				if(!empty($item->metadata["__command_handler"]) && $item->metadata["__command_handler"] == $command)
					unset($this->instance->events["channelcommand"][$key]);
			}
		}
		
		return $this;
	}
	
	
	/**
	 *	With the exception of events, all functionality in the bot is handled with Modules.
	 */
	public function __call($method, $arguments)
	{
		$list = Module\Stack::getInstance();
		$name = strtolower($method);
		
		if(!empty($list[$name]))
		{
			array_unshift($arguments, $this->context);
			
			return call_user_func_array($list[$name], $arguments);
		}
		
		return null;
	}
}