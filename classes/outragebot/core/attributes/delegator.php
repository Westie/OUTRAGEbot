<?php
/**
 *	Delegator trait for Sequential - creating easier ways of complicating code
 *	beyond all belief.
 */


namespace OUTRAGEbot\Core\Attributes;


trait Delegator
{
	/**
	 *	Uhm... since static things don't work how I want them to work, I'll just
	 *	make a private property instead.
	 */
	private $disable_setter = false;
	
	
	/**
	 *	Called when a non-existant property is requested.
	 */
	public final function &__get($property)
	{
		$reflector = $this->reflector();
		$property = ctype_digit($property) ? (integer) $property : $property;
		
		$result = null;
		
		$this->disable_setter = true;
		
		if($reflector->hasMethod("getter"))
			$result = $reflector->getMethod("getter")->invoke($this, $property);
		else
			$result = $this->invokeGetter($property);
		
		$this->disable_setter = false;
		
		return $result;
	}
	
	
	/**
	 *	Called when a non-existant property is saved.
	 */
	public final function __set($property, $value)
	{
		$reflector = $this->reflector();
		$property = ctype_digit($property) ? (integer) $property : $property;
		
		if($this->disable_setter)
			return $this->{$property} = $value;
		
		if($reflector->hasMethod("setter"))
			return $reflector->getMethod("setter")->invoke($this, $property);
		
		return $this->invokeSetter($property, $value);
	}
	
	
	/**
	 *	Called to check if a virtual property exists.
	 */
	public final function __isset($property)
	{
		$reflector = $this->reflector();
		$property = ctype_digit($property) ? (integer) $property : $property;
		
		if($reflector->hasMethod("isset"))
			return $reflector->getMethod("isset")->invoke($this, $property);
		
		return $this->invokeIsset($property);
	}
	
	
	/**
	 *	Called to remove a virtual property.
	 */
	public final function __unset($property)
	{
		$reflector = $this->reflector();
		$property = ctype_digit($property) ? (integer) $property : $property;
		
		if($reflector->hasMethod("unset"))
			return $reflector->getMethod("unset")->invoke($this, $property);
		
		return $this->invokeUnset($property);
	}
	
	
	/**
	 *	This method is in charge of invoking the getter delegator associated with
	 *	the Delegator trait.
	 *
	 *	See further: [const OC_MAGIC_IN_CONTAINER]
	 */
	protected final function &invokeGetter($property)
	{
		$reflector = $this->reflector();
		
		if($reflector->hasMethod("getter_".$property))
		{
			$method = $reflector->getMethod("getter_".$property);
			
			if($method->returnsReference())
				return $method->invoke($this);
			
			$return = $method->invoke($this);
			return $return;
		}
		
		if($reflector->hasConstant("OC_MAGIC_IN_CONTAINER") && $reflector->getConstant("OC_MAGIC_IN_CONTAINER") === true)
		{
			if(isset($this->container[$property]))
				return $this->container[$property];
		}
		
		$null = null; return $null;
	}
	
	
	/**
	 *	This method is in charge of invoking the setter delegator associated with
	 *	the Delegator trait.
	 *
	 *	See further: [const OC_MAGIC_IN_CONTAINER]
	 */
	protected final function invokeSetter($property, $value)
	{
		$reflector = $this->reflector();
		
		if($reflector->hasMethod("setter_".$property))
			return $reflector->getMethod("setter_".$property)->invoke($this);
		
		if($reflector->hasConstant("OC_MAGIC_IN_CONTAINER") && $reflector->getConstant("OC_MAGIC_IN_CONTAINER") === true)
			return $this->container[$property] = $value;
		
		return $this->{$property} = $value;
	}
	
	
	/**
	 *	This method is in charge of invoking the isset delegator associated with
	 *	the Delegator trait.
	 *
	 *	See further: [const OC_MAGIC_IN_CONTAINER]
	 */
	protected final function invokeIsset($property)
	{
		$reflector = $this->reflector();
		
		if($reflector->hasMethod("isset_".$property))
			return $reflector->getMethod("isset_".$property)->invoke($this);
		
		if($reflector->hasConstant("OC_MAGIC_IN_CONTAINER") && $reflector->getConstant("OC_MAGIC_IN_CONTAINER") === true)
			return isset($this->container[$property]);
		
		return false;
	}
	
	
	/**
	 *	This method is in charge of invoking the unset delegator associated with
	 *	the Delegator trait.
	 *
	 *	See further: [const OC_MAGIC_IN_CONTAINER]
	 */
	protected final function invokeUnset($property)
	{
		$reflector = $this->reflector();
		
		if($reflector->hasMethod("unset_".$property))
			return $reflector->getMethod("unset_".$property)->invoke($this);
		
		if($reflector->hasConstant("OC_MAGIC_IN_CONTAINER") && $reflector->getConstant("OC_MAGIC_IN_CONTAINER") === true)
			unset($this->container[$property]);
		
		return false;
	}
	
	
	/**
	 *	Get a reflection of this class. You'll be needing the $reflector
	 *	property, look in the Delegations trait.
	 */
	public function reflector()
	{
		return new \ReflectionObject($this);
	}
}