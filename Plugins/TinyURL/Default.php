<?php
/**
 *	TinyURL example made by MaVe.
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */


class TinyURL extends Plugins
{
	public
		$pTitle   = "TinyURL",
		$pAuthor  = "MaVe",
		$pVersion = "1.0";
		
		
        /* These functions are called when the plugin is loaded or unloaded */
        public function onConstruct()
        {
        }

        public function onDestruct()
        {
        }

        public function onMessage($sNickname, $sChannel, $sMessage)
        {
                $aMessage = explode(" ", $sMessage);
                foreach ($aMessage as $sMessage)
                {
                        $sRegex = '/http:\/\/tinyurl.com\/(.*)/';
                        preg_match($sRegex, $sMessage, $aMatches);
                        
                        if (!empty($aMatches))
                        {
                                $sTinycode = trim($aMatches[1]);
                                $sWebsite = file_get_contents('http://preview.tinyurl.com/' . $sTinycode);
                                $sWebsite = str_replace("\r", "", $sWebsite);
                                $sWebsite = str_replace("\t", "", $sWebsite);
                                $sRegex = '/This TinyURL redirects to:\n<blockquote><b>(.*?)<br \/><\/b>/U';
                                preg_match($sRegex, $sWebsite, $aMatches2);
                                $sRedirect = trim(str_replace("<br />", "", $aMatches2[1]));
                                $this->Message($sChannel, $sNickname . ": This TinyURL redirects to " . $sRedirect);
                                break;
                        }
                }
        }
}

?>
