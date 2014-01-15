<?php
/**
 *	Singleton trait for OUTRAGEbot - provides a simple way
 *	for classes to be loaded only once.
 */


namespace OUTRAGEbot\Core\Attributes;


trait Singleton
{
	/**
	 *	Retrieve this instance.
	 */
	public static function getInstance()
	{
		$target = __CLASS__;
		
		static $instance = null;
		return $instance ?: $instance = new $target();
	}
	
	
	/**
	 *	Prevent cloning of this object.
	 */
	public function __clone()
	{
		trigger_error("Cloning ".__CLASS__." is not allowed.", E_USER_ERROR);
		return false;
	}
	
	
	/**
	 *	Prevent de-serialisation of this object, which could bring about another
	 *	cloning scenario.
	 */
	public function __wakeup()
	{
		trigger_error("Unserializing ".__CLASS__." is not allowed.", E_USER_ERROR);
		return false;
	}
}