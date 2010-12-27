<?php
/**
 *	OUTRAGEbot development
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
		
		return Core::addInstance($sConfigName, new CoreMaster($pConfig));
	}
}