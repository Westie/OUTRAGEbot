<?php
/**
 *	Youtube example made by MaVe.
 *
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */


class Translation extends Plugins
{
	public
		$pTitle   = "Translation",
		$pAuthor  = "Westie",
		$pVersion = "1.0";
		
		
        /* These functions are called when the plugin is loaded or unloaded */
        public function onConstruct()
        {
        	$this->addHandler("COMMAND", "getTranslation", "trans");
        }

        public function getTranslation($sNickname, $sChannel, $sArguments)
        {
        	if(!$sArguments)
        	{
        		$this->Notice($sNickname, "USAGE: trans [fromLang] [toLang] [Message]");
		}
		
		$aArguments = explode(' ', $sArguments, 3);
		$sURL = "http://ajax.googleapis.com/ajax/services/language/translate?v=1.0&q=".rawurlencode($aArguments[2]).
		"&langpair=".rawurlencode($aArguments[0].'|'.$aArguments[1]);
		
		$sJSON = file_get_contents($sURL);
		
		if(preg_match("/{\"translatedText\":\"([^\"]+)\"/i", $sJSON, $aMatches))
		{
			$this->Message($sChannel, $aMatches[1]);
		}
        }
}

?>
