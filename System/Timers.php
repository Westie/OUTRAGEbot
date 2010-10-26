<?php
/**
 *	Timers class for OUTRAGEbot
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 2.0.0-Alpha (Git commit: )
 */


class Timers
{
	/* Variables needed */
	private static
		$aTimers = array();
	
	public static
		$sCurrentTimer;
	
	
	/* Function to create a function-timer */
	static function Create($cCallback, $fInterval, $iRepeat, $aArguments = array())
	{
		$aTimer = array();
		
		$aTimer['KEY'] = substr(sha1(time()."-".uniqid()), 4, 10);
		
		$aTimer['CALLBACK'] = $cCallback;
		$aTimer['INTERVAL'] = (float) $fInterval;
		$aTimer['REPEAT'] = $iRepeat;
		$aTimer['CALLTIME'] = ((float) microtime(true) + (float) $fInterval);
		$aTimer['ARGUMENTS'] = (array) $aArguments;
		
		self::$aTimers[] = $aTimer;
		return $aTimer['KEY'];
	}
	
	
	/* Function to delete a timer */
	static function Delete($sKey)
	{
		foreach(self::$aTimers as $sKey => $aTimer)
		{
			if(($sKey == '*') || ($aTimer['KEY'] == $sKey))
			{
				unset(self::$aTimers[$sKey]);
				return true;
			}
		}
		return false;
	}
	
	
	/* Function to get the details of a timer key via an array. */
	static function Get($sKey)
	{
		foreach(self::$aTimers as $aTimer)
		{
			if($aTimer['KEY'] == $sKey)
			{
				return $aTimer;
			}
		}
		return array();
	}


	/* Some verification is needed. */
	static function CheckCall()
	{
		foreach(self::$aTimers as $sKey => $aTimer)
		{
			if(!is_callable($aTimer['CALLBACK']))
			{
				/* 'Preety' obvious no calls here */
				unset(self::$aTimers[$sKey]);
				continue;
			}
		}
	}
	
	
	/* Function to loop through the timers */
	static function Scan()
	{
		if(count(self::$aTimers) == 0)
		{
			return;
		}
		
		foreach(self::$aTimers as $sKey => &$aTimer)
		{
			if(microtime(true) >= $aTimer['CALLTIME'])
			{
				self::$sCurrentTimer = $sKey;
				call_user_func_array($aTimer['CALLBACK'], (array) $aTimer['ARGUMENTS']);
				
				$iTimes = (isset($aTimer['REPEAT']) ? (int) $aTimer['REPEAT'] : 0);
				
				if($iTimes != -1)
				{
					--$iTimes;
					
					if($iTimes > 0)
					{
						$aTimer['CALLTIME'] = ((float) microtime(true) + $aTimer['INTERVAL']);
						$aTimer['REPEAT'] = $iTimes;
					}
					else
					{
						unset(self::$aTimers[$sKey]);
					}
				}
				else
				{
					$aTimer['CALLTIME'] = ((float) microtime(true) + $aTimer['INTERVAL']);
				}
			}
		}
		
		self::$sCurrentTimer = "";
	}
}

?>
