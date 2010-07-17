<?php
/**
 *	StaticLibrary class for OUTRAGEbot
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC3 (Git commit: 65be068b5593bd12cfbc84c4727a9758a672c0a7)
 */


class StaticLibrary
{
	/**
	 *	Function to remove the useless characters from the chunks.
	 */
	static function sortChunks($aChunks)
	{
		$aChunks[0] = isset($aChunks[0]) ? ($aChunks[0][0] == ":" ? substr($aChunks[0], 1) : $aChunks[0]) : "";
		$aChunks[1] = isset($aChunks[1]) ? $aChunks[1] : "";
		$aChunks[2] = isset($aChunks[2][0]) ? ($aChunks[2][0] == ":" ? substr($aChunks[2], 1) : $aChunks[2]) : "";
		$aChunks[3] = isset($aChunks[3][0]) ? ($aChunks[3][0] == ":" ? substr($aChunks[3], 1) : $aChunks[3]) : "";
		
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
			elseif($$pBot->aRequestConfig['TIMEOUT'] < time())
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
			
			if($aPluginCache['MTIME'] <= filemtime($sDirname))
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
}