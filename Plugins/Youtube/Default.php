<?php
/**
 *	Youtube example made by MaVe.
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */


class Youtube extends Plugins
{
	public
		$pTitle   = "Youtube",
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
                        $sRegex = '@http://www.youtube.com/watch\?v=(.*)@';
                        preg_match($sRegex, $sMessage, $aMatches);
                        if (!empty($aMatches))
                        {
                                $sWatchcode = trim($aMatches[1]);
                                $nPos = strpos($sWatchcode, "&");
                                if ($nPos !== FALSE)
                                        $sWatchcode = substr($sWatchcode, 0, $nPos);
                                $sWebsite = file_get_contents('http://www.youtube.com/watch?v=' . $sWatchcode);
                                $sWebsite = str_replace("\r", "", $sWebsite);
                                $sWebsite = str_replace("\t", "", $sWebsite);

                                $sTitleRegex = '@<title>\nYouTube\n- (.*?)\n</title>@U';
                                preg_match($sTitleRegex, $sWebsite, $aTitleMatches);
                                $sTitle = trim($aTitleMatches[1]);
                                $this->Message($sChannel, Format::Bold . $sNickname . Format::Bold . ": Video '" . $sTitle . "'");
                                break;
                        }
                }
        }
}

?>
