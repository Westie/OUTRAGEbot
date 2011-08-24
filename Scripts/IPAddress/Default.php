<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *			inspired by Luke (or, PwnFlakes)
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     0638fa8bb13e1aca64885a4be9e6b7d78aab0af7
 *	Committed at:   Wed Aug 24 23:16:56 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class IPAddress extends Script
{
	public function onConstruct()
	{
		$this->addCommandHandler("ip", "getIPAddress");
	}


	public function getIPAddress($sChannel, $sNickname, $sArguments)
	{
		if(!$sArguments)
		{
			$this->Notice($sNickname, "Error: you need to give an IP address!");
			return;
		}

		$pXML = simplexml_load_file("http://api.hostip.info/?ip={$sArguments}");

		$aCountries = $pXML->xpath("/HostipLookupResultSet/gml:featureMember/Hostip");

		if($aCountries === false)
		{
			$sChannel("Invalid IP!");
		}
		else
		{
			$sChannel("The associated country is {c:darkgreen}{b}".ucwords(strtolower($aCountries[0]->countryName)), FORMAT);
		}
	}
}
