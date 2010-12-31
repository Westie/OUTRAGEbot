<?php
/**
 *	OUTRAGEbot development
 */


abstract class Plugin
{
	private
		$pInstance = null;
	
	
	/**
	 *	This is called when the plugin is loaded.
	 */
	public final function __construct($pInstance)
	{
		$this->pInstance = $pInstance;
		
		call_user_func(array($this, 'onConstruct'));
		return true;
	}
	
	
	/**
	 *	This is called when the plugin is removed.
	 */
	public final function __destruct()
	{
		call_user_func(array($this, 'onDestruct'));
		
		foreach($this as $sKey => $sValue)
		{
			$this->$sKey = NULL;
		}
		
		return true;
	}
	
	
	/**
	 *	Called when any other undefined method is called.
	 */
	public final function __call($sFunctionName, $aArgumentList)
	{		
		if(method_exists($this->pInstance, $sFunctionName))
		{
			return call_user_func_array(array($this->pInstance, $sFunctionName), $aArgumentList);
		}
		else
		{
			// The handler crap - I'll sort that out!
		}
		
		return null;
	}
	
	
	/**
	 *	Retrieve objects from the Master object.
	 */
	public final function __get($sKey)
	{
		if(property_exists($this->pInstance, $sKey))
		{
			return $this->pInstance->$sKey;
		}
		
		return null;
	}
}