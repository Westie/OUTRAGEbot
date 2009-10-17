<?php
/**
 *	QuoteFFS plugin for OUTRAGEbot.
 *
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */

class QuoteFFS extends Plugins
{
	public function onCommand($sNickname, $sChannel, $sCommand, $sArguments)
	{
		if(!strcmp($sCommand, "quoteffs"))
		{
			if(!$sArguments)
			{
				$this->Notice($sNickname, "USAGE: @quoteffs [QuoteID]");
				return true;
			}
			
			$sWebsite = file_get_contents('http://www.quoteffs.com/?'.$sArguments);
			$sRegex = '/<td class="body">(.*?)<\/td>/s';
			
			preg_match($sRegex, $sWebsite, $aMatches);
			
			$sQuote = html_entity_decode($aMatches[1]);
			$sQuote = str_replace('<br />', '', $sQuote);
			$aQuote = explode("\n", $sQuote);
			
			if(count($aQuote) >= 5)
			{
				$this->Notice($sNickname, "Quote has too many lines, cannot be printed.");
				return true;
			}
			
			foreach($aQuote as $sQuote)
			{
				$this->Message($sChannel, trim($sQuote));
			}
			
			return true;
		}
		
		return false;
	}
}
