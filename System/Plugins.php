<?php
/**
 *	Plugins class for OUTRAGEbot
 *
 *	This class contains the key plugin functions, plus modified functions
 *	that are included in the Master class that removes the need for arrays
 *	when using callbacks.
 *
 *	To look at the callbacks that plugins natively recieved, look at the
 *	'debug02' plugin.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 2.0.0-Alpha (Git commit: )
 */

abstract class Plugins
{
	/**
	 *	Log level - the higher the level, the more severe the problem that
	 *	is allowed to get logged.
	 */
	public $pLogLevel = 1;
	
	
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
	public $pBot = false;
	
	
	/**
	 *	An array of the internal names of the plugin, namely the
	 *	plugins virtual name, and its real, unique name.
	 *
	 *	@ignore
	 *	@var array
	 */
	public $aIdentifier = array();
	
	
	/**
	 *	This is called when the plugin is loaded.
	 *
	 *	@ignore
	 */
	public final function __construct($pResource, $aIdentifier)
	{
		$this->pBot = $pResource;
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
		
		foreach($this as $sKey => $sValue)
		{
			$this->$sKey = NULL;
		}
		
		return true;
	}
	
	
	/**
	 *	This is called when the bot tries to call a function that doesn't exist.
	 *
	 *	@ignore
	 */
	public final function __call($sFunction, $aArguments)
	{
		$this->pBot->sLastAccessedPlugin = $this->aIdentifier[0];
		
		if(is_callable($aFunction = array($this->pBot, $sFunction)))
		{
			return call_user_func_array($aFunction, $aArguments);
		}
		else
		{
			if(isset($this->pBot->aFunctions[$sFunction]))
			{
				$cCallback = array($this->pBot->getPlugin($this->pBot->aFunctions[$sFunction][0]), $this->pBot->aFunctions[$sFunction][1]);
				return call_user_func_array($cCallback, $aArguments);
			}
			
			return null;
		}
	}
	
	
	/**
	 *	This is called when the plugin tries to get a variable that doesn't exist.
	 *
	 *	@todo Get array info from the config if necessary.
	 *	@ignore
	 */
	public final function __get($sKey)
	{
		if(isset($this->pBot->$sKey))
		{
			return $this->pBot->$sKey;
		}
		else
		{
			return null;
		}
	}
	
	
	/**
	 *	Called when you try and call an object.
	 *
	 *	@param string $sRawString Raw IRC output
	 */
	public final function __invoke($sRawString)
	{
		$this->pBot->Raw($sRawString);
	}
	
	
	/**
	 *	Function that is used to write to print to console.
	 *
	 *	@todo Might enable printing to a file.
	 *	@param string $sString String to echo.
	 *	@param integer $iLogLevel Log level.
	 */
	public final function Log($sString, $iLogLevel = 1)
	{
		if($iLogLevel >= $this->pLogLevel)
		{
			echo "[{$this->aIdentifier[0]}]: {$sString}".PHP_EOL;
		}
		
		return true;
	}
	
	
	/**
	 *	Returns the configuration that is for the plugin.
	 *
	 *	<code>$aConfig = $this->getConfig();</code>
	 *
	 *	You can test this with the default plugin by requesting this
	 *	function from the Evaluation plugin.
	 *
	 *	@return array Configuration.
	 */
	public final function getConfig()
	{
		return isset($this->pBot->oConfig->{$this->aIdentifier[0]}) ? $this->pBot->oConfig->{$this->aIdentifier[0]} : null;
	}
	
	/**
	 *	Returns plugin name.
	 *	@see Master::getPluginName
	 */
	public final function __getName()
	{
		return $this->aIdentifier[0];
	}
}

?>
