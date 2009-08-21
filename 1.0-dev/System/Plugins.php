<?php
/**
 *	Plugins class for OUTRAGEbot
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2009 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0
 */

abstract class Plugins
{
	/**
	 *	Title of the plugin.
	 *
	 *	@var string
	 */
	public $pTitle = "";
	
	
	/**
	 *	Author of the plugin.
	 *
	 *	@var string
	 */
	public $pAuthor = "";
	
	
	/**
	 *	Version of the plugin.
	 *
	 *	@var string
	 */
	public $pVersion = "";
	
	
	/**
	 *	The parent class of the plugin.
	 *
	 *	@ignore
	 *	@var string
	 */
	public $oBot = false;
	
	
	/**
	 *	The internal name of the plugin, used in getting configs.
	 *
	 *	@ignore
	 *	@var array
	 */
	private $aIdentifier = array();
	
	
	/**
	 *	This is called when the plugin is loaded.
	 *
	 *	@ignore
	 */
	public final function __construct($oResource, $aIdentifier)
	{
		$this->oBot = $oResource;
		$this->aIdentifier = $aIdentifier;
		
		call_user_func(array($this, 'onConstruct'));
		return true;
	}
	
	
	/**
	 *	This is called when the plugin is removed.
	 *
	 *	@ignore
	 */
	public final function __destruct()
	{
		call_user_func(array($this, 'onDestruct'));
		$this->oBot = null;
		return true;
	}
	
	
	/**
	 *	This is called when the bot tries to call a function that doesn't exist.
	 *
	 *	@ignore
	 */
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
	
	
	/**
	 *	This is called when the plugin tries to get a variable that doesn't exist.
	 *
	 *	@ignore
	 */
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
	
	
	/**
	 *	Function that is used to write to print to console.
	 *
	 *	@todo Might enable printing to a file.
	 *	@param string $sString String to echo.
	 */
	public final function Log($sString)
	{
		echo "[{$this->aIdentifier[0]}]: {$sString}".PHP_EOL;
		return true;
	}
	
	
	/**
	 *	Returns the 
	 *
	 *	@return array Configuration that is supplied with the bot.
	 */
	public final function getConfig()
	{
		return $this->oBot->oConfig->{$this->aIdentifier[0]};
	}
}

?>
