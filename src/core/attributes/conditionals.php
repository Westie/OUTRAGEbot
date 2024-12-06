<?php
/**
 *	The Conditional trait provides support for testing elements.
 *
 *	This particular trait is configured to test objects, however, this
 *	can easily be extended with extending this trait.
 *
 *	This doesn't depend on ->container functionality, instead it uses
 *	the ArrayAccess interface. Makes things cleaner I suppose!
 */


namespace OUTRAGEbot\Core\Attributes;


trait Conditionals
{
	/**
	 *	Provides a test to check if this object can be equally compared
	 *	to another object.
	 */
	public function is($target)
	{
		if(is_object($target))
			return ($this->class == $target->class) && ($this->id == $target->id);
		
		return false;
	}
	
	
	/**
	 *	Provides a test to iterate through this object to see if a target object
	 *	is within its stack.
	 */
	public function has($target)
	{
		foreach($this as $item)
		{
			if(($item->class == $target->class) && ($item->id == $target->id))
				return true;
		}
		
		return false;
	}
}