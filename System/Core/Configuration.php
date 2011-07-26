<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     34505731494ce4358c897884a185e6869f52bc08
 *	Committed at:   Tue Jul 26 23:19:16 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreConfiguration
{
	/**
	 *	Loads a configuration file from a specific location.
	 */
	static function ParseLocation($sLocation)
	{
		$sConfigName = substr(basename($sLocation), 0, -4);

		if($sConfigName[0] == "~")
		{
			return false;
		}

		$aConfiguration = parse_ini_file($sLocation, true);

		if(!is_array($aConfiguration) || count($aConfiguration) <= 1)
		{
			println(" * Sorry, looks like the network {$sConfigName} failed to load!");
			return false;
		}

		$pConfig = new stdClass();

		$bSlave = false;

		foreach($aConfiguration as $sConfigKey => $aConfigObject)
		{
			if($sConfigKey[0] == "~")
			{
				$sConfigKey = substr($sConfigKey, 1);
				$pConfig->$sConfigKey = (object) $aConfigObject;

				continue;
			}

			$aConfigObject = array_merge(array("nickname" => $sConfigKey), $aConfigObject, array("slave" => $bSlave));
			$pConfig->Bots[$sConfigKey] = (object) $aConfigObject;

			$bSlave = true;
		}

		$sInstance = $sConfigName.' (#'.uniqid().')';
		self::verifyConfiguration($pConfig, $sInstance);

		Core::addInstance($sInstance, new CoreMaster($pConfig));
	}


	/**
	 *	Ensures that the required variables are indeed in memory.
	 */
	static function verifyConfiguration($pConfig, $sInstance)
	{
		$pConfig->Server = new stdClass();
		$pNetwork = $pConfig->Network;

		if(empty($pNetwork->delimiter))
		{
			$pNetwork->delimiter = "!";
		}

		if(empty($pNetwork->rotation))
		{
			$pNetwork->rotation = SEND_DEF;
		}

		if(empty($pNetwork->quitmsg))
		{
			$pNetwork->quitmsg = "OUTRAGEbot is going to bed :(";
		}

		if(empty($pNetwork->version))
		{
			$pNetwork->version = "OUTRAGEbot ".BOT_VERSION." (rel. ".BOT_RELDATE."); David Weston; http://outrage.typefish.co.uk";
		}

		if(empty($pNetwork->perform))
		{
			$pNetwork->perform = array();
		}

		$pNetwork->ownerArray = array();
		$pNetwork->scriptArray = array();
		$pNetwork->channelArray = array();

		if(!empty($pNetwork->owners))
		{
			foreach(explode(',', $pNetwork->owners) as $sOwnerAddress)
			{
				$pNetwork->ownerArray[] = trim($sOwnerAddress);
			}
		}

		if(!empty($pNetwork->channels))
		{
			foreach(explode(',', $pNetwork->channels) as $sChannelName)
			{
				$pNetwork->channelArray[] = trim($sChannelName);
			}
		}

		if(!empty($pNetwork->scripts))
		{
			foreach(explode(',', $pNetwork->scripts) as $sScriptName)
			{
				$pNetwork->scriptArray[] = trim($sScriptName);
			}
		}

		$pConfig->sInstance = $sInstance;

		return $pConfig;
	}
}
