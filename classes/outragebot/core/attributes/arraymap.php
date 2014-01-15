<?php
/**
 *	ArrayMap trait for OUTRAGEbot - maps array functions to any given
 *	object, useful for standardising functionality across many different
 *	classes.
 */


namespace OUTRAGEbot\Core\Attributes;


trait ArrayMap
{
	/**
	 *	We'll define our standard variable in our map - the container.
	 */
	protected $container = [];
	
	
	/**
	 *	Called to return the first index of this array.
	 */
	public final function first()
	{
		$set = array_slice($this->container, 0, 1, true);
		
		return isset($set[0]) ? $set[0] : null;
	}
	
	
	/**
	 *	Called to return the last index of this array.
	 */
	public final function last()
	{
		$set = array_slice($this->container, -1, 1, true);
		
		return isset($set[0]) ? $set[0] : null;
	}
	
	
	/**
	 *	Push an item into the internal container.
	 */
	public final function push($value)
	{
		$this->container[] = $value;
		return $this;
	}
	
	
	/**
	 *	Shift an item from the internal container.
	 */
	public final function shift()
	{
		return array_shift($this->container);
	}
	
	
	/**
	 *	Shift an item from the internal container.
	 */
	public final function unshift($value)
	{
		array_unshift($this->container, $value);
		return $this;
	}
	
	
	/**
	 *	Removes and returns the last entry of the container.
	 */
	public final function pop($value)
	{
		return array_pop($this->container);
	}
	
	
	/**
	 *	Slices the internal container - this will not reset the pointer.
	 */
	public final function slice($offset = 0, $length = null, $preserve_keys = false)
	{
		return array_slice($this->container, $offset, $length, $preserve_keys);
	}
	
	
	/**
	 *	Splices the internal container - this will however reset the
	 *	internal pointer.
	 */
	public final function splice($offset, $length = 0, $replacement = null)
	{
		return array_splice($this->container, $offset, $length, $replacement);
	}
	
	
	/**
	 *	Iterator - but in a function.
	 */
	public final function each($callback)
	{
		foreach($this->container as $index => $element)
		{
			$callback($index, $element);
		}
		
		return $this;
	}
	
	
	/**
	 *	Return a map of this element's iterator.
	 */
	public final function map($callback = null)
	{
		return $callback ? array_map($callback, $this->container) : $this->toArray(false);
	}
	
	
	/**
	 *	Called to shuffle the contents of this container.
	 */
	public function shuffle()
	{
		shuffle($this->container);
		
		return $this;
	}
}