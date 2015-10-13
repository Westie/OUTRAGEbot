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
	 *	What is our infinite constant?
	 */
	const INFINITE_TIMER = -1;
	
	
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
		$this->introduceMethod("setInterval", "setInterval");
		$this->introduceMethod("setTimeout", "setTimeout");
		$this->introduceMethod("removeTimer", "remove");
	}
	
	
	/**
	 *	Create a timer, with the ability to fine tune how many times you want the timer
	 *	to be called.
	 *
	 *	@param callback	$callback  Timer handler
	 *	@param double $interval    Interval in seconds (can support fractional seconds)
	 *	@param integer $repeat     How many times to repeat the call, or -1 for infinite
	 *	@param array $arguments    Array of arguments passed to the timer function.
	 *
	 *	@return	string             Timer ID
	 *
	 *	@example input documentation/examples/timers/timer-add-1.txt
	 *	@example input documentation/examples/timers/timer-add-2.txt
	 */
	public function add($context, $callback, $interval, $repeat = 1, $arguments = [])
	{
		$callback = $this->toTimerClosure($context->callee, $callback);
		
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
	 *	Creates an interval, being called approximately every n seconds.
	 *
	 *	@param callback	$callback  Timer handler
	 *	@param double $interval    Interval in seconds (can support fractional seconds)
	 *	@param array $arguments    Array of arguments passed to the timer function.
	 *
	 *	@return	string             Timer ID
	 *
	 *	@example input documentation/examples/timers/timer-add-3.txt
	 */
	public function setInterval($context, $callback, $interval, $arguments = [])
	{
		return $this->add($context, $callback, $interval, self::INFINITE_TIMER, $arguments);
	}
	
	
	/**
	 *	Creates a timeout, being called approximately every n seconds.
	 *
	 *	@param callback	$callback  Timer handler
	 *	@param double $interval    Interval in seconds (can support fractional seconds)
	 *	@param array $arguments    Array of arguments passed to the timer function.
	 *
	 *	@return	string             Timer ID
	 *	
	 *	@example input documentation/examples/timers/timer-add-4.txt
	 */
	public function setTimeout($context, $callback, $interval, $arguments = [])
	{
		return $this->add($context, $callback, $interval, 0, $arguments);
	}
	
	
	/**
	 *	Called when removing a timer.
	 *
	 *	@param string $index  Timer ID
	 *	@return	boolean
	 */
	public function remove($context, $index)
	{
		if(!isset($this->timers[$index]))
			return false;
		
		unset($this->timers[$index]);
		return true;
	}
	
	
	/**
	 *	Called whenever we want to poll for event timers.
	 */
	public function poll()
	{
		$time = microtime(true);
		
		foreach($this->timers as $index => &$timer)
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
			
			if($timer["repeat"] != self::INFINITE_TIMER)
			{
				--$timer["repeat"];
				
				if($timer["repeat"] < 1)
					unset($this->timers[$index]);
			}
			
			unset($timer);
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
			if(is_array($callback))
			{
				$reflection = new \ReflectionObject($callback[0]);
				
				if($reflection->hasMethod($callback[1]))
					return $reflection->getMethod($callback[1])->getClosure($callback[0]);
			}
			elseif(is_string($callback) && method_exists($context, $callback))
			{
				$reflection = new \ReflectionObject($context);
				
				if($reflection->hasMethod($callback))
					return $reflection->getMethod($callback)->getClosure($context);
			}
			else
			{
				return (new \ReflectionFunction($callback))->getClosure();
			}
		}
		
		return null;
	}
}