<?php
/**
 *	Timer module for OUTRAG3bot.
 */


namespace OUTRAGEbot\Module\Modules;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Module;
use \OUTRAGEbot\Connection;


class Timer extends Module\Template
{
	/**
	 *	Stores all active timers.
	 */
	private $timers = [];
	
	
	/**
	 *	Called when the module has been loaded into memory.
	 */
	public function construct()
	{
		$this->introduceMethod("addTimer", "add");
		$this->introduceMethod("removeTimer", "remove");
	}
	
	
	/**
	 *	Called when adding a timer.
	 *
	 *	@param callback	$callback  Timer handler
	 *	@param double $interval    Interval in seconds (can support fractional seconds)
	 *	@param integer $repeat     How many times to repeat the call, or -1 for infinite
	 *	@param array $arguments    Array of arguments passed to the timer function.
	 *
	 *	@return	string             Timer ID	 
	 */
	public function add($context, $callback, $interval, $repeat = 1, $arguments = [])
	{
		$callback = $this->toTimerClosure($context->caller, $callback);
		
		if(!$callback)
			return false;
		
		$index = sha1(microtime());
		$interval = (float) $interval;
		
		$this->timers[$index] = array
		(
			"id" => $index,
			"context" => $context,
			"callback" => $callback,
			"interval" => $interval,
			"repeat" => $repeat,
			"arguments" => $arguments,
			"next" => microtime(true) + $interval,
		);
		
		return $index;
	}
	
	
	/**
	 *	Called when removing a timer.
	 *
	 *	@param string $index  Timer ID
	 *	@return	boolean
	 */
	public function remove($context, $index)
	{
		unset($this->timers[$index]);
		
		return true;
	}
	
	
	/**
	 *	Called whenever we want to poll for event timers.
	 */
	public function poll()
	{
		$time = microtime(true);
		
		foreach($this->timers as $index => $timer)
		{
			if($timer["next"] > $time)
				continue;
			
			if(empty($timer["arguments"]))
			{
				if($timer["callback"]() === true)
					$timer["repeat"] = 0;
			}
			else
			{
				if(call_user_func_array($timer["callback"], $timer["arguments"]) === true)
					$timer["repeat"] = 0;
			}
			
			$time = microtime(true);
			$timer["next"] = $time + $timer["interval"];
			
			if($timer["repeat"] == -1)
				continue;
			
			if($timer["repeat"] == 0)
				unset($this->timers[$index]);
		}
		
		return true;
	}
	
	
	/**
	 *	Turns callback into a closure.
	 */
	protected function toTimerClosure($context, $callback)
	{
		if($callback instanceof \Closure)
		{
			return $callback;
		}
		else
		{
			if(method_exists($context, $callback))
			{
				$reflection = new \ReflectionObject($context);
				
				if($reflection->hasMethod($callback))
					return $reflection->getMethod($callback)->getClosure($context);
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