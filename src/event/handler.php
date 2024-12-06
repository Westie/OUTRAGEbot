<?php
/**
 *	Event template class for OUTRAG3bot
 */


namespace OUTRAGEbot\Event;

use \OUTRAGEbot\Core;
use \OUTRAGEbot\Element;
use \OUTRAGEbot\Module;


class Handler
{
	/**
	 *	Stores the script context that the event came from.
	 */
	private $context = null;
	
	
	/**
	 *	Stores the event name and namespace that this refers to.
	 */
	private $event = null;
	
	
	/**
	 *	Stores the callback handler that this handler invokes.
	 */
	private $handler = null;
	
	
	/**
	 *	This stores metadata about this event handler. If you don't think
	 *	you need this, then quite simply, you don't.
	 */
	public $metadata = [];
	
	
	/**
	 *	Called when the handler has been created.
	 */
	public function __construct($context, $event, $handler, $metadata = [])
	{
		$this->context = $context;
		$this->event = $event;
		$this->metadata = $metadata;
		
		if($handler instanceof \Closure)
		{
			$this->handler = $handler->bindTo($context);
		}
		else
		{
			if(is_array($handler))
			{
				$reflection = new \ReflectionObject($handler[0]);
				
				if($reflection->hasMethod($handler[1]))
					$this->handler = $reflection->getMethod($handler[1])->getClosure($handler[0]);
			}
			else
			{
				$reflection = new \ReflectionObject($context);
				
				if($reflection->hasMethod($handler))
					$this->handler = $reflection->getMethod($handler)->getClosure($context);
			}
		}
		
		return true;
	}
	
	
	/**
	 *	Invokes this event handler.
	 */
	public function invoke($event, array $params = null)
	{
		if(!$params)
			$params = [ $event ];
		
		if($this->context instanceof Module\Template)
		{
			$context = new Element\Context();
			$context->instance = $event->instance;
			
			array_unshift($params, $context);
		}
		
		return call_user_func_array($this->handler, $params) !== false;
	}
	
	
	/**
	 *	Retrieves the event name that this handler refers to.
	 */
	public function getEventName()
	{
		return $this->event;
	}
	
	
	/**
	 *	Retrieves the context that this event is assigned to.
	 */
	public function getContext()
	{
		return $this->context;
	}
}