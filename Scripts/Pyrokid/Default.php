<?php
/**
 *	A little class designed to make Pyrokid be pissed.
 */


class Pyrokid extends Script
{
	public function onChannelMessage($sChannel, $sNickname, $sMessage)
	{
		if(stristr($sMessage, "hungary"))
		{
			$sChannel("I am sorry for nick alerting you. :(");
		}
	}
}