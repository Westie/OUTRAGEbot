<?php
/**
 *	StaticLibrary class for OUTRAGEbot
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC1 (Git commit: 81ab23ac872fb1a8c0ecbfe32a31b6bd7576c833)
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
}