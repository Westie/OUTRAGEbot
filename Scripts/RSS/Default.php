<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		Jannis Pohl <mave1337@gmail.com>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     85afeb688f7ca5db50b99229665ff01e8cec8868
 *	Committed at:   Sun Jan 30 19:41:46 2011 +0000
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class RSS extends Script
{
	private
		$aSubscriptions = array();

	private static
		$sBitlyLogin = '',
		$sBitlyApiKey = '';

	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$ps3 = array('#ps3');
		$this->addSubscription('ps3news', 'http://feeds.feedburner.com/ps3news/KXsI?format=xml', 60, $ps3);
		$this->addSubscription('psx-scene', 'http://www.psx-scene.com/forums/external.php?type=RSS2&forumids=6', 60, $ps3);
		$this->addSubscription('ps3-hacks', 'http://www.ps3-hacks.com/feed/', 60, $ps3);
	}


	/**
	 *	Called when the Script is removed.
	 */
	public function onDestruct()
	{
		foreach ($this->aSubscriptions as $aSubscription)
		{
			$this->removeTimer($aSubscription['timerKey']);
		}
	}

	private function secureMessage($sTarget, $sMessage)
	{
		$sMessage = str_replace(array("\r", "\n"), "", $sMessage);
		return $this->Message($sTarget, $sMessage);
	}

	private static function cleanString($string)
	{
		// :(
        	return str_replace(array("&#8220;", "&#8221;"), '"', $string);
	}

	private static function shortenUrl($sURL)
	{
		$sShort = file_get_contents("http://api.bit.ly/v3/shorten?login=" . self::$sBitlyLogin . "&apiKey=" . self::$sBitlyApiKey . "&longUrl=" . urlencode($sURL) ."&format=txt");
		return strlen($sShort) <= 7 || substr($sShort, 0, 7) != "http://" ? 'N/A' : $sShort;
	}

	private static function getRssFeed($sURL, $iNum = 5)
	{
	        $aNews = array();

	        $sData = file_get_contents($sURL);
	        if (strpos($sData, "</item>") > 0)
	        {
	                preg_match_all("/<item.*>(.+)<\/item>/Uism", $sData, $aItems);
	                $iAtom = 0;
	        }
	        elseif (strpos($data, "</entry>") > 0)
	        {
	                preg_match_all("/<entry.*>(.+)<\/entry>/Uism", $sData, $aItems);
	                $iAtom = 1;
	        }

	        preg_match("/<?xml.*encoding=\"(.+)\".*?>/Uism", $sData, $aEncoding);
	        $sEncoding = $aEncoding[1];

	        $i = 0;
	        foreach ($aItems[1] as $sItem)
	        {
	                if ($i == $iNum)
	                {
	                        break;
	                }
	                ++$i;

	                preg_match("/<title.*>(.+)<\/title>/Uism", $sItem, $aTitle);
	                if ($iAtom == 0)
	                {
	                        preg_match("/<link>(.+)<\/link>/Uism", $sItem, $aLink);
	                }
	                elseif ($iAtom == 1)
	                {
	                        preg_match("/<link.*alternate.*text\/html.*href=[\"\'](.+)[\"\'].*\/>/Uism", $aItem, $aLink);
	                }

	                /*if ($atom == 0)
	                {
	                        preg_match("/<description>(.*)<\/description>/Uism", $item, $description);
	                }
	                elseif ($atom == 1)
	                {
	                        preg_match("/<summary.*>(.*)<\/summary>/Uism", $item, $description);
	                }*/

	                $aTitle = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $aTitle);
	                //$description = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $description);
	                $aLink = preg_replace('/<!\[CDATA\[(.+)\]\]>/Uism', '$1', $aLink);

	                $sTitle = html_entity_decode(self::cleanString($aTitle[1]), ENT_QUOTES, $sEncoding);
	                $sLink = html_entity_decode(self::cleanString($aLink[1]), ENT_QUOTES, $sEncoding);

	                $aNews[] = array($sTitle, $sLink);
	        }

	        return $aNews;
	}

	private static function getLatestNews($sURL)
	{
		$aNews = self::getRssFeed($sURL, 1);
		return $aNews[0];
	}

	public function timerCallback($iTimer)
	{
		$aTimer =& $this->aSubscriptions[$iTimer];

		$aLatestNews = self::getLatestNews($aTimer['url']);
		if (($sHash = md5($aLatestNews[0])) != $aTimer['latestNewsHash'])
		{
			$aTimer['latestNewsHash'] = $sHash;

			$sNews = $aLatestNews[0];
			if (strlen($sNews) > 300)
			{
				$sNews = substr($sNews, 0, 300) . ' ' . Format::Colour . '14...' . Format::Colour;
			}

			$sMessage = Format::Colour . '14[' . Format::Colour . '15rss' . Format::Colour . '14] ' . Format::Colour . '10('
					. Format::Colour . '11' . $aTimer['prefix'] . Format::Colour . '10)' . Format::Colour . ' '
					. $sNews . ' ' . Format::Colour . '14::' . Format::Colour . ' ' . self::shortenUrl($aLatestNews[1]);

			foreach ($aTimer['messageTargets'] as $sTarget)
			{
				$this->secureMessage($sTarget, $sMessage);
			}
		}
	}

	private function addSubscription($sPrefix, $sURL, $iInterval, $aMessageTargets)
	{
		$aLatestNews = self::getLatestNews($sURL);

		$sLatestNewsHash = md5($aLatestNews[0]);

		$iNewTimer = count($this->aSubscriptions);
		$sTimerKey = $this->addTimer(array($this, 'timerCallback'), $iInterval, -1, array($iNewTimer));
		$this->aSubscriptions[$iNewTimer] = array
		(
			"prefix" => $sPrefix,
			"url" => $sURL,
			"messageTargets" => $aMessageTargets,
			"latestNewsHash" => $sLatestNewsHash,
			"timerKey" => $sTimerKey
		);
	}
}
