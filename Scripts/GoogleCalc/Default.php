<?php
/**
 *	OUTRAGEbot development
 *	Script created by mavus
 */


class GoogleCalc extends Script
{
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
        {
		$sCommand = strtolower($sCommand);
		
                if ($sCommand == "gcalc")
		{
			$sContents = file_get_contents("http://www.google.com/search?ie=UTF-8&q=" . urlencode($sArguments));
			if (preg_match("@<h2 class=r style=\"font-size:(\d+)%\"><b>(.*)</b></h2>@U", $sContents, $aMatches) != 0)
			{
				$sResult = $aMatches[2];
				$sResult = preg_replace("@<sup>(-{0,1})(\d+)</sup>@U", "^$1$2", $sResult);
				$sResult = html_entity_decode($sResult, ENT_COMPAT, "UTF-8");
				$sResult = strip_tags($sResult);

				$this->Message($sChannel, "[gcalc] " . $sResult);
			}
			else
			{
				$this->Message($sChannel, "[gcalc] Invalid request");
			}
			
			return END_EVENT_EXEC;
		}
	}
}