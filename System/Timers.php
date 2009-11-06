<?php
/**
 *	Timers class for OUTRAGEbot
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2009 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0-RC2
 */


class Timers
{
	/* Variables needed */
	private static
		$aTimers = array();
	
	
	/* Function to create a function-timer */
	static function Create($cCallback, $fInterval, $iRepeat, $aArguments = array())
	{
		if(!is_callable($cCallback))
		{
			return false;
		}

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
		foreach(self::$aTimers as $iKey => $aTimer)
		{
			if($aTimer['KEY'] == $sKey)
			{
				unset(self::$aTimers[$iKey]);
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
			if($aTimer['sKey'] == $sKey)
			{
				return $aTimer;
			}
		}
		return array();
	}


	/* Some verification is needed. */
	static function CheckCall()
	{
		foreach(self::$aTimers as $iKey => $aTimer)
		{
			if(!is_callable($aTimer['CALLBACK']))
			{
				/* 'Preety' obvious no calls here */
				unset(self::$aTimers[$iKey]);
				continue;
			}
		}
	}
	
	
	/* Function to loop through the timers */
	static function Scan()
	{
		if(count(self::$aTimers) == 0)
		{
			return false;
		}
		
		foreach(self::$aTimers as $iKey => &$aTimer)
		{
			if(microtime(true) >= $aTimer['CALLTIME'])
			{
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
						unset(self::$aTimers[$iKey]);
					}
				}
				else
				{
					$aTimer['CALLTIME'] = ((float) microtime(true) + $aTimer['INTERVAL']);
				}
			}
		}
	}
}

?>
