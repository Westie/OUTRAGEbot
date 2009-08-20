<?php
/**
 *	Timers class for OUTRAGEbot
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2009 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0
 */


class Timers
{
	/* Variables needed */
	private static
		$aTimers = array();
	
	
	/* Function to create a function-timer */
	static function Create($cCallback, $iInterval, $iRepeat, $aArguments = array())
	{
		if(!is_callable($cCallback))
		{
			return false;
		}

		$aTimer = array();
		
		$aTimer['sKey']       = substr(sha1(time()."-".uniqid()), 4, 10);
		
		$aTimer['cCallback']  = $cCallback;
		$aTimer['iInterval']  = $iInterval;
		$aTimer['iRepeat']    = $iRepeat;
		$aTimer['iCallTime']  = (time() + $iInterval);
		$aTimer['aArguments'] = (array) $aArguments;
		
		self::$aTimers[] = $aTimer;
		return $aTimer['sKey'];
	}
	
	
	/* Function to delete a timer */
	static function Delete($sKey)
	{
		foreach(self::$aTimers as $iKey => $aTimer)
		{
			if($aTimer['sKey'] == $sKey)
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
	
	
	/* Function to loop through the timers */
	static function Scan()
	{
		if(count(self::$aTimers) == 0)
		{
			return false;
		}
		
		foreach(self::$aTimers as $iKey => &$aTimer)
		{
			if(time() >= $aTimer['iCallTime'])
			{
				call_user_func_array($aTimer['cCallback'], (array) $aTimer['aArguments']);
				
				$iTimes = (isset($aTimer['iRepeat']) ? (int) $aTimer['iRepeat'] : 0);
				
				if($iTimes != -1)
				{
					--$iTimes;
					
					if($iTimes > 0)
					{
						$aTimer['iCallTime'] = (time() + $aTimer['iInterval']);
						$aTimer['iRepeat'] = $iTimes;
					}
					else
					{
						unset(self::$aTimers[$iKey]);
					}
				}
				else
				{
					$aTimer['iCallTime'] = (time() + $aTimer['iInterval']);
				}
			}
		}
	}
}

?>
