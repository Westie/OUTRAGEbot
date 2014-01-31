<?php
/**
 *	Module element for OUTRAG3bot.
 *
 *	Use this functionality to add extra features to OUTRAG3bot that are designed
 *	to be used in scripts, as opposed to just being a script.
 */


namespace OUTRAGEbot\Module;


use OUTRAGEbot\Connection;
use OUTRAGEbot\Core;
use OUTRAGEbot\Core\Attributes;
use OUTRAGEbot\Event;
use OUTRAGEbot\Module;


abstract class Template
{
	/**
	 *	Called whenever the module has been loaded into memory.
	 */
	public final function __construct()
	{
		return $this->construct();
	}
	
	
	/**
	 *	Wrapper for construct...
	 */
	public function construct()
	{
		return true;
	}
	
	
	/**
	 *	Called to introduce a method into the global modules list.
	 */
	public function introduceMethod($name, $callback = null, $force = false)
	{
		if($callback == null)
			$callback = $name;
		
		if(!is_array($name))
			$name = array($name);
		
		$list = Module\Stack::getInstance();
		
		foreach($name as $item)
		{
			$item = strtolower($item);
			
			if($force || !isset($list[$item]))
				$list[$item] = $this->toClosure($callback);
		}
		
		# used to generate documentation, won't really be used
		# anywhere else...
		# should I comment out in commits?
		if(defined("OUTRAGEbot_DEBUG"))
		{
			if(!isset($this->__methods))
				$this->__methods = [];
			
			$reflection = new \ReflectionObject($this);
			
			foreach($name as $item)
			{
				if($reflection->hasMethod($callback))
					$this->__methods[$item] = $reflection->getMethod($callback);
			}
		}
		
		return $this;
	}
	
	
	/**
	 *	Called to bind an event handler to this script.
	 */
	public function on(Connection\Instance $instance, $event, $handler, $metadata = [])
	{
		$matches = [];
		
		if(!$metadata)
			$metadata = [];
		
		$metadata["__module_source_instance"] = get_class($this);
		
		preg_match("/^(.*?)(\.(.*?))?$/", $event, $matches);
		
		if($matches)
			$type = (string) $matches[1];
		
		if(!isset($instance->events[$type]))
			$instance->events[$type] = new Core\ObjectContainer();
		
		$instance->events[$type]->push(new Event\Handler($this, $event, $handler, $metadata));
		
		return $this;
	}
	
	
	/**
	 *	Called to remove event handlers that belong to this script.
	 */
	public function off(Connection\Instance $instance, $event)
	{
		foreach($instance->events as $type => $struct)
		{
			foreach($struct as $key => $item)
			{
				if($item->getContext() == $this)
					unset($instance->events[$type][$key]);
			}
		}
		
		return $this;
	}
	
	
	/**
	 *	Turns callback into a closure.
	 */
	protected function toClosure($callback)
	{
		if($callback instanceof \Closure)
		{
			return $callback;
		}
		else
		{
			if(method_exists($this, $callback))
			{
				$reflection = new \ReflectionObject($this);
				
				if($reflection->hasMethod($callback))
					return $reflection->getMethod($callback)->getClosure($this);
			}
			elseif(is_array($callback))
			{
				$reflection = new \ReflectionObject($callback[0]);
				
				if($reflection->hasMethod($callback[1]))
					return $reflection->getMethod($callback[1])->getClosure($callback[0]);
			}
			else
			{
				return (new \ReflectionFunction($callback))->getClosure();
			}
		}
		
		return null;
	}
}