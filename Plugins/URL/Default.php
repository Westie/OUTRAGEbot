<?php
/*
 *
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */


class URL extends Plugins
{
	public
		$pTitle   = "URL",
		$pAuthor  = "Anthony & Westie",
		$pVersion = "1.1";


        public function onMessage($sNickname, $sChannel, $sMessage)
        {
                $aMessage = explode(" ", $sMessage);
		
                foreach($aMessage as $sString)
                {
			$iMatched = preg_match("/http\:\/\/(.*)/", $sString, $aMatches);
			
			if(!$iMatched)
			{
				continue;
			}
			
			$sWebpage = file_get_contents($aMatches[0]);
			
			$iTitleMatched = preg_match("/<title>(.*?)<\/title>/", $sWebpage, $aTitle);
			
			if($iTitleMatched)
			{
				$this->Message($sChannel, "URL: {$aMatches[0]}   Title: {$aTitle[1]}");
			}
                }
        }
}

?>