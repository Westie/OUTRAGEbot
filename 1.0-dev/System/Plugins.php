<?php
/**
 *	Plugins class for OUTRAGEbot
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2009 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0
 */

abstract class Plugins
{
	public	
		$pTitle   = "",
		$pAuthor  = "",
		$pVersion = "",
		$oBot = false;
	
	private
		$sIdentifier;
		
	
	public final function __construct($oResource, $sIdentifier)
	{
		$this->oBot = $oResource;
		$this->sIdentifier = $sIdentifier;
		
		call_user_func(array($this, 'onConstruct'));
		return true;
	}
	
	
	public final function __destruct()
	{
		call_user_func(array($this, 'onDestruct'));
		$this->oBot = null;
		return true;
	}
	
	
	public final function __call($sFunction, $aArguments)
	{
		if(is_callable($aFunction = array($this->oBot, $sFunction)))
		{
			return call_user_func_array($aFunction, $aArguments);
		}
		else
		{
			return;
		}
	}
	
	
	public final function __get($sKey)
	{
		if(isset($this->oBot->$sKey))
		{
			return $this->oBot->$sKey;
		}
		else
		{
			return null;
		}
	}
	
	
	public final function Log($sString)
	{
		echo "[plugin] {$sString}".PHP_EOL;
		return true;
	}
}

?>
