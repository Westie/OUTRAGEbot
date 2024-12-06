<?php
/**
 *	ObjectContainer class for OUTRAG3bot - simple way to change the functionality
 *	of magic functions and make it easier to add getters and setters without
 *	having to resort to chaining in different classes.
 *
 *	It is also default functionality to access newly created properties as a part of
 *	the stored pairs - to cancel this functionality just create your own set of methods
 *	such as these: [getter, setter, isset, unset] - the mere existance of one of these
 *	will cause the ObjectContainer functionality of that particular method to be
 *	cancelled/ignored.
 *
 *	If you wish to re-enable this while keeping the object magic functions, then you
 *	can call parent::invokeX, where 'X' denotes the delegator type (getter, etc. see
 *	above).
 *
 *	There is no way to cancel the array accessing and iteration of this class, because
 *	that is the main point of this class.
 */


namespace OUTRAGEbot\Core;


class ObjectContainer implements \ArrayAccess, \Countable, \Iterator, \Serializable
{
	/**
	 *	Include our delegator - this will provide getter/setter support
	 *	across all the scopes.
	 */
	use Attributes\Delegator;
	use Attributes\Delegations;
	use Attributes\ArrayMap;
	use Attributes\Conditionals;
	
	
	/**
	 *	We can use this constant to determine whether the standard magic getters shall
	 *	populate the object, or populate the container.
	 */
	const OC_MAGIC_IN_CONTAINER = true;
	
	
	/**
	 *	An array containing what is stored here. We don't want anyone
	 *	to be able to directly edit this easily.
	 *
	 *	$container is stored in Attributes\ArrayMap, needs to be added
	 *	back here whenever that bug is fixed.
	 *		-> https://bugs.php.net/bug.php?id=62537
	 *
	 *	protected $container = [];
	 */
	
	
	/**
	 *	I'd like to return an array representation of this set.
	 */
	public final function toArray($recursive = true)
	{
		if(!$recursive)
		{
			return $this->container;
		}
		
		$array = [];
		
		foreach($this->container as $property => $item)
		{
			if($item instanceof ObjectContainer)
				$array[$property] = $item->toArray();
			else
				$array[$property] = $item;
		}
		
		return $array;
	}
	
	
	/**
	 *	I'd like to also return an object representation of this set.
	 */
	public final function toObject()
	{
		return $this->container;
	}
	
	
	/**
	 *	This is only to be used in very, very carefully considered situations.
	 */
	protected final function &getContainerReference()
	{
		return $this->container;
	}
	
	
	/**
	 *	Populate the container from an array or object.
	 */
	public final function populateContainer($container)
	{
		if(is_array($container))
		{
			$this->container = $container;
		}
		elseif(is_object($container))
		{
			$this->container = get_object_vars($container);
		}
		
		return true;
	}
	
	
	/**
	 *	Use an array reference as the container.
	 */
	public final function populateContainerFromReference(array &$container)
	{
		$this->container = &$container;
		return true;
	}
	
	
	/**
	 *	Populates the container from an array or an object, changing the
	 *	type of an object to this class recursively.
	 */
	public final function populateContainerRecursively($container)
	{
		foreach($container as $property => $item)
		{
			if(is_array($item))
			{
				$this[$property] = new self();
				$this[$property]->populateContainerRecursively($item);
			}
			elseif(is_object($item))
			{
				$item = get_object_vars($item);
				
				$this[$property] = new self();
				$this[$property]->populateContainerRecursively($item);
			}
			else
			{
				$this[$property] = $item;
			}
		}
	}
	
	
	/**
	 *	Resets/totally empties this object.
	 */
	public final function resetContainer()
	{
		$this->container = [];
	}
	
	
	/**
	 *	Checks if the internal container contains a specific key.
	 */
	public final function hasContainerProperty($property)
	{
		return array_key_exists($property, $this->container);
	}
	
	
	/**
	 *	ArrayAccess interface: Checks if an offset exists.
	 */
	public final function offsetExists($property)
	{
		return isset($this->container[$property]);
	}
	
	
	/**
	 *	ArrayAccess interface: Retrieves an offset.
	 */
	public final function &offsetGet($property)
	{
		if(isset($this->container[$property]))
		{
			return $this->container[$property];
		}
		
		$null = null; return $null;
	}
	
	
	/**
	 *	ArrayAccess interface: Gives an offset a value.
	 */
	public final function offsetSet($property, $value)
	{
		if($property === "")
			return $this->container[] = $value;
		
		return $this->container[$property] = $value;
	}
	
	
	/**
	 *	ArrayAccess interface: Removes an offset from the array.
	 */
	public final function offsetUnset($property)
	{
		unset($this->container[$property]);
		return true;
	}
	
	
	/**
	 *	Countable interface: Counts the amount of accessable properties.
	 */
	public final function count()
	{
		return count($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the current accessed property.
	 */
	public final function current()
	{
		return current($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the current accessed key.
	 */
	public final function key()
	{
		return key($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the next property.
	 */
	public final function next()
	{
		return next($this->container);
	}
	
	
	/**
	 *	Iterator interface: Returns the previous property.
	 */
	public final function rewind()
	{
		return reset($this->container);
	}
	
	
	/**
	 *	Iterator interface: Checks if the internal array is valid.
	 */
	public final function valid()
	{
		return current($this->container);
	}
	
	
	/**
	 *	Serializable interface: Returns a serialised representation of
	 *	the the current accessable pairs.
	 */
	public final function serialize()
	{
		return serialize($this->container);
	}
	
	
	/**
	 *	Serializable interface: Unserialised the string into the 
	 *	local accessable cache.
	 */
	public final function unserialize($container)
	{
		$this->container = unserialize($container);
		return true;
	}
}