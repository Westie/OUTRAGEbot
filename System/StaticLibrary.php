<?php
/**
 *	StaticLibrary class for OUTRAGEbot
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-BETA7 (Git commit: d6e9046fbd12d660ded19c7b71c3e13c577d5adc)
 */


class StaticLibrary
{
	/**
	 *	Function to remove the useless characters from the chunks.
	 */
	static function sortChunks($aChunks)
	{
		for($iIndex = 0; $iIndex < 4; ++$iIndex)
		{
			if(isset($aChunks[$iIndex]))
			{
				if($aChunks[$iIndex][0] == ":")
				{
					$aChunks[$iIndex] = substr($aChunks[$iIndex], 1);
				}
			}
			else
			{
				$aChunks[$iIndex] = "";
			}
		}
		
		return $aChunks;
	}
	
	
	/**
	 *	Internal: To do with the parsing of the queues
	 *
	 *	@ignore
	 */
	static function sortQueue($pBot, $aRaw, $sMessage)
	{
		if($pBot->aRequestConfig['TIMEOUT'] !== false)
		{				
			if(array_search($aRaw[1], $pBot->aRequestConfig['ENDNUM']) !== false)
			{
				$pBot->aMessageQueue[] = $sMessage;
				$pBot->iUseQueue = false;
			}
			elseif($pBot->aRequestConfig['TIMEOUT'] < time())
			{
				$pBot->aMessageQueue[] = $sMessage;
				$pBot->iUseQueue = false;
			}
		}
		
		if(array_search($aRaw[1], $pBot->aRequestConfig['SEARCH']) === false)
		{
			$pBot->aMessageQueue[] = $sMessage;
		}
		else
		{
			$pBot->aRequestOutput[] = $sMessage;
		}
	}
	
	
	/**
	 *	Internal: Parsing and modifying the plugin files.
	 *
	 *	@ignore
	 */
	static function getPluginIdentifier($sPluginName)
	{
		$sDirname = BASE_DIRECTORY."/Plugins/{$sPluginName}/Default.php";

		if(!file_exists($sDirname))
		{
			return false;
		}
		
		if(isset(Control::$aPluginCache[$sPluginName]))
		{
			$aPluginCache = Control::$aPluginCache[$sPluginName];
			
			clearstatcache();
			
			if($aPluginCache['MTIME'] >= filemtime($sDirname))
			{
				return $aPluginCache['IDENT'];
			}
		}

		$sIdentifier = substr($sPluginName, 0, 8).'_'.substr(sha1(microtime()."-".uniqid()), 2, 10);
		$sClass = file_get_contents($sDirname); // Ouch, this has gotta hurt.

		if(!preg_match("/class[\s]+?".$sPluginName."[\s]+?extends[\s]+?Plugins[\s]+?{/", $sClass))
		{
			return false;
		}
			
		$sClass = preg_replace("/(class[\s]+?)".$sPluginName."([\s]+?extends[\s]+?Plugins[\s]+?{)/", "\\1".$sIdentifier."\\2", $sClass);
		$sFile = tempnam(dirname($sDirname), "nat"); // Stops the __FILE__ bugs.
		
		file_put_contents($sFile, $sClass);				
		unset($sClass); // Weight off the shoulders anyone?
			
		include $sFile;
		unlink($sFile);
		
		Control::$aPluginCache[$sPluginName] = array
		(
			'MTIME' => filemtime($sDirname),
			'IDENT' => $sIdentifier,
		);
		
		return $sIdentifier;
	}
	
	
	/**
	 *	Internal: Function to get the date since something.
	 *
	 *	@ignore
	 */
	static function dateSince($iDate1, $iDate2 = 0)
	{
		if(!$iDate2)
		{
			$iDate2 = time();
		}

   		$aDifferences = array
		(
			'SECONDS' => 0,
			'MINUTES'=> 0,
			'HOURS' => 0,
			'DAYS' => 0,
			'WEEKS' => 0,
			
			'TOTAL_SECONDS' => 0,
			'TOTAL_MINUTES' => 0,
			'TOTAL_HOURS' => 0,
			'TOTAL_DAYS' => 0,
			'TOTAL_WEEKS' => 0,
		);

		if($iDate2 > $iDate1)
		{
			$iTemp = $iDate2 - $iDate1;
		}
		else
		{
			$iTemp = $iDate1 - $iDate2;
		}

		$iSeconds = $iTemp;

		$aDifferences['WEEKS'] = floor($iTemp / 604800);
		$iTemp -= $aDifferences['WEEKS'] * 604800;

		$aDifferences['DAYS'] = floor($iTemp / 86400);
		$iTemp -= $aDifferences['DAYS'] * 86400;

		$aDifferences['HOURS'] = floor($iTemp / 3600);
		$iTemp -= $aDifferences['HOURS'] * 3600;

		$aDifferences['MINUTES'] = floor($iTemp / 60);
		$iTemp -= $aDifferences['MINUTES'] * 60;

		$aDifferences['SECONDS'] = $iTemp;
		
		$aDifferences['TOTAL_WEEKS'] = floor($iSeconds / 604800);
		$aDifferences['TOTAL_DAYS'] = floor($iSeconds / 86400);
		$aDifferences['TOTAL_HOURS'] = floor($iSeconds / 3600);
		$aDifferences['TOTAL_MINUTES'] = floor($iSeconds / 60);
		$aDifferences['TOTAL_SECONDS'] = $iSeconds;

		return $aDifferences;
	}
	
	
	/**
	 *	Replaces the character with a letter in a mode string.
	 *
	 *	@param string Characters
	 *	@return string Letters
	 */
	static function modeCharToLetter($sModeString)
	{
		return str_replace
		(
			array
			(
				'+',
				'%',
				'@',
				'&',
				'~',
			),
			
			array
			(
				'v',
				'h',
				'o',
				'a',
				'q',
			),
			
			$sModeString
		);
	}
	
	
	/**
	 *	Replaces the letter with a character in a mode string.
	 *
	 *	@param string Letters
	 *	@return string Characters
	 */
	static function modeLetterToChar($sModeString)
	{
		return str_replace
		(
			array
			(
				'v',
				'h',
				'o',
				'a',
				'q',
			),
			
			array
			(
				'+',
				'%',
				'@',
				'&',
				'~',
			),
			
			$sModeString
		);
	}
}