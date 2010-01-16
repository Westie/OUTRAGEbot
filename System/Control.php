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
 *	@version 1.0.0
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
	 *	@ignore
	 */
	public static $aStack;
	
	
	/**
	 *	Creates a bot from an INI file in the config folder.
	 *	
	 *	@param string $sConfig Bot-group 
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
	 *	@param string $sConfig Bot-group
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
	
	
	/**
	 *	Sends a raw IRC message from a selected bot(-group) with various settings.
	 *
	 *	@param string $sConfig Bot-group
	 *	@param string $sMessage IRC message to send from that bot.
	 */
	static function botSend($sConfig, $sMessage)
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
	static function botGetInfo($sConfig = false)
	{
		$aReturn = array();
		
		if($sConfig == false)
		{
			foreach(self::$aBots as $sBotGroup => $oBot)
			{
				$aTemp = array();
				
				foreach($oBot->aBotObjects as $iReference => $oSocket)
				{
					$aTemp['Socket.'.$iReference] = array
					(
						"NICKNAME" => $oSocket->aConfig['nickname'],
						"USERNAME" => $oSocket->aConfig['username'],
						"REALNAME" => $oSocket->aConfig['realname'],
						"STATISTICS" => $oSocket->aStatistics,
					);
				}
				
				$aReturn[$sBotGroup] = $aTemp;
			}
		}
		else
		{
			if(isset(self::$aBots[$sConfig]))
			{		
				foreach(self::$aBots[$sConfig]->aBotObjects as $iReference => $oSocket)
				{
					$aReturn['Socket.'.$iReference] = array
					(
						"NICKNAME" => $oSocket->aConfig['nickname'],
						"USERNAME" => $oSocket->aConfig['username'],
						"REALNAME" => $oSocket->aConfig['realname'],
						"STATISTICS" => $oSocket->aStatistics,
					);
				}
			}
		}
		
		return $aReturn;
	}
}
	
