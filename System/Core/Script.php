<?php
/**
 *	OUTRAGEbot development
 */


abstract class Script
{
	private
		$pInstance = null;
	
	
	public
		$sScriptID;
	
	
	/**
	 *	This is called when the Script is loaded.
	 */
	public final function __construct($pInstance, $sScriptID)
	{
		$this->pInstance = $pInstance;
		$this->sScriptID = $sScriptID;
		
		$this->onConstruct();
		return true;
	}
	
	
	/**
	 *	This is called when the Script is removed.
	 */
	public final function __destruct()
	{
		call_user_func(array($this, 'onDestruct'));
		
		foreach($this as $sKey => $sValue)
		{
			$this->$sKey = NULL;
		}
		
		$this->onDestruct();
		return true;
	}
	
	
	/**
	 *	Called when any other undefined method is called.
	 */
	public final function __call($sFunctionName, $aArgumentList)
	{
		$this->pInstance->pCurrentScript = $this;
		
		if(method_exists($this->pInstance, $sFunctionName))
		{
			return call_user_func_array(array($this->pInstance, $sFunctionName), $aArgumentList);
		}
		else
		{
			if(isset(Core::$pFunctionList->$sFunctionName))
			{
				call_user_func_array(Core::$pFunctionList->$sFunctionName, $aArgumentList);
			}
		}
		
		$this->pInstance->pCurrentScript = null;
		
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