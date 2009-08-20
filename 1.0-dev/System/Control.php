<?php
/**
 *	Control class for OUTRAGEbot
 *
 *	The real brain of the bot, this controls everything. This static hosts all of the bots, and all their files.
 *	If you want to access a bot, this is the class to use.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2009 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0
 */


class Control
{
	/**
	 *	@ignore
	 */
	public static $aBots;
	
	
	/**
	 *	@ignore
	 */
	public static $oConfig;
	
	
	/**
	 *	@ignore
	 */
	public static $oGlobals;
	
	
	/**
	 *	Creates a bot from an INI file in the config folder.
	 *	
	 *	@param string $sConfig
	 *	@uses ConfigParser::parseConfigFile()
	 */
	static function botCreate($sConfig)
	{
		if(file_exists(BASE_DIRECTORY."/Configuration/{$sConfig}.ini"))
		{
			self::$oConfig->parseConfigFile(BASE_DIRECTORY."/Configuration/{$sConfig}.ini");
		}
	}
	
	
	/**
	 *	Kills a bot(-group) where from the name of its config file.
	 *	
	 *	@param string $sConfig
	 *	@return bool 'true' on success.
	 */
	static function botRemove($sConfig)
	{
		if(isset(self::$aBots[$sConfig]))
		{
			self::$aBots[$sConfig]->_onDestruct();
			unset(self::$aBots[$sConfig]);
			return true;
		}
		
		return false;
	}
}
	