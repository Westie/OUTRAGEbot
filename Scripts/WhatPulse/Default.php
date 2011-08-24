<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     72dcd361ddbd66db711dbc45552ab766bc6bbf84
 *	Committed at:   Wed Aug 24 23:26:52 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class WhatPulse extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$this->addCommandHandler("wp", "getWPStats");
		$this->addCommandHandler("setwp", "setWPID");
	}


	/**
	 *	Called when someone wants to set their WP stats.
	 */
	public function setWPID($sChannel, $sNickname, $sArguments)
	{
		if(!$sArguments)
		{
			$this->Notice($sNickname, "Sorry, but this doesn't look a valid ID");
			return END_EVENT_EXEC;
		}

		$pUser = $this->getResource("Users/{$sNickname}", "w");
		$pUser->write($sArguments);

		$this->Notice($sNickname, "Congrats! You've now set your WP ID to {$sArguments}.");

		return END_EVENT_EXEC;
	}


	/**
	 *	Called when someone wants their WP stats.
	 */
	public function getWPStats($sChannel, $sNickname, $sArguments)
	{
		$sPerson = $sNickname;

		if($sArguments)
		{
			$sNickname = $sArguments;
		}

		if(!$this->isResource("Users/{$sNickname}"))
		{
			$this->Notice($sPerson, "Nope, you don't have an WP id with us, use setwp.");
			return END_EVENT_EXEC;
		}

		$pWhatpulse = $this->getWhatpulseObject($sNickname);

		if(!($pWhatpulse instanceof SimpleXMLElement))
		{
			$this->Notice($sPerson, "There seems to be an error. Sorry about that!");
			return END_EVENT_EXEC;
		}

		$sDate = $pWhatpulse->DateJoined;
		$sNickname = $pWhatpulse->AccountName;
		$iKeys = number_format("{$pWhatpulse->TotalKeyCount}");
		$iClicks = number_format("{$pWhatpulse->TotalMouseClicks}");
		$iMouseDistance = number_format("{$pWhatpulse->TotalMiles}");
		$iRank = number_format("{$pWhatpulse->Rank}");
		$iPulses = number_format("{$pWhatpulse->Pulses}");

		$iDelta = (strtotime(date("Y-m-d")) - strtotime($pWhatpulse->DateJoined)) / 86400;
		$iKeyAvg = number_format((string) ($pWhatpulse->TotalKeyCount / $iDelta));

		$this->Message($sChannel, "Since ".Format::DarkGreen."{$sDate}".Format::Clear.", ".Format::DarkGreen."{$sNickname}".Format::Clear." has typed ".Format::DarkGreen."{$iKeys}".Format::Clear." characters, clicked ".Format::DarkGreen."{$iClicks}".Format::Clear." times and moved their mouse ".Format::DarkGreen."{$iMouseDistance}".Format::Clear." miles.");
		$this->Message($sChannel, "This gives ".Format::DarkGreen."{$sNickname}".Format::Clear." an average of ".Format::DarkGreen."{$iKeyAvg}".Format::Clear." keys per day.");
		$this->Message($sChannel, Format::DarkGreen."{$sNickname}".Format::Clear." has sent ".Format::DarkGreen."{$iPulses}".Format::Clear." pulses during this time, giving them a rank of ".Format::DarkGreen."{$iRank}".Format::Clear.".");

		return END_EVENT_EXEC;
	}


	/**
	 *	Function to deal with the output of the WP stats
	 */
	private function getWhatpulseObject($sNickname)
	{
		$pUser = $this->getResource("Users/{$sNickname}");
		$iUserID = $pUser->read();
		unset($pUser);

		$sXML = file_get_contents("http://whatpulse.org/api/user.php?UserID={$iUserID}");

		if(!stristr($sXML, "<?xml"))
		{
			return null;
		}

		return new SimpleXMLElement($sXML);
	}
}
