<?php
/**
 *	Control class for OUTRAGEbot
 *
 *	The real brain of the bot, this controls everything. This static hosts all of the bots, and all their files.
 *	If you want to access a bot, this is the class to use.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC1 (Git commit: 81ab23ac872fb1a8c0ecbfe32a31b6bd7576c833)
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
	public static $pConfig;
	
	
	/**
	 *	@ignore
	 */
	public static $pGlobals;
	
	
	/**
	 *	@ignore
	 */
	public static $aStack;
	
	
	/**
	 *	Creates a bot from an INI file in the config folder.
	 *	
	 *	@param string $sConfig Bot-group 
	 *	@uses ConfigParser::parseConfigFile()
	 */
	static function createBot($sConfig)
	{
		if(file_exists(BASE_DIRECTORY."/Configuration/{$sConfig}.ini"))
		{
			self::$pConfig->parseConfigFile(BASE_DIRECTORY."/Configuration/{$sConfig}.ini");
		}
	}
	
	
	/**
	 *	Kills a bot(-group) where from the name of its config file.
	 *	
	 *	@param string $sConfig Bot-group
	 *	@return bool 'true' on success.
	 */
	static function removeBot($sConfig)
	{
		if(isset(self::$aBots[$sConfig]))
		{
			self::$aBots[$sConfig]->_onDestruct();
			unset(self::$aBots[$sConfig]);
			return true;
		}
		
		return false;
	}
	
	
	/**
	 *	Sends a raw IRC message from a selected bot(-group) with various settings.
	 *
	 *	@param string $sConfig Bot-group
	 *	@param string $sMessage IRC message to send from that bot.
	 */
	static function sendFromBot($sConfig, $sMessage)
	{
		if(isset(self::$aBots[$sConfig]))
		{
			self::$aBots[$sConfig]->sendRaw($sMessage);
		} 
	}
	
	
	/**
	 *	This function gets a list of the active (loaded) bots, and their associated children.
	 *	This also returns a pretty big array, so it is advised against printing it in channels.
	 *
	 *	@param string $sConfig Bot-group - optional.
	 *	@return array Array of details.
	 */
	static function getBotInfo($sConfig = false)
	{
		$aReturn = array();

		foreach(self::$aBots as $sBotGroup => $pBot)
		{
			if($sConfig != false && $sConfig != $sBotGroup)
			{
				continue;
			}
			
			$aTemp = array();
			
			foreach($pBot->aBotObjects as $iReference => $pSocket)
			{
				$aTemp['Socket.'.$iReference] = array
				(
					"NICKNAME" => $pSocket->aConfig['nickname'],
					"USERNAME" => $pSocket->aConfig['username'],
					"REALNAME" => $pSocket->aConfig['realname'],
					"STATISTICS" => $pSocket->aStatistics,
				);
			}
			
			$aReturn[$sBotGroup] = $aTemp;
		}
		
		return $aReturn;
	}
	
	
	/**
	 *	Retrieves the names of all the bot groups loaded.
	 *
	 *	@return array List of all bot groups.
	 */
	static function getBotNames()
	{
		return array_keys(self::$aBots);
	} 
	
	
	/**
	 *	Retrieve the bot-group as an object.
	 *
	 *	@param string $sConfig.
	 *	@return object
	 */
	static function getBotObject($sConfig)
	{
		if(isset(self::$aBots[$sConfig]))
		{
			return self::$aBots[$sConfig];
		}
		
		return null;
	}
}
