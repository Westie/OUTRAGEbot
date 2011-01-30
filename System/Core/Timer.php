<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:	release name
 *	Git commit:	commit hash
 *	Committed at:	update time
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreTimer
{
	private static
		$aTimers = array();
	
	
	/**
	 *	Called when the module is loaded.
	 */
	static function initModule()
	{		
		Core::introduceFunction("addTimer", array(__CLASS__, "Add"));
		Core::introduceFunction("removeTimer", array(__CLASS__, "Remove"));
	}
	
	
	/**
	 *	Called on every loop iteration.
	 */
	static function onTick()
	{
		if(count(self::$aTimers) == 0)
		{
			return;
		}
		
		foreach(self::$aTimers as $sTimerKey => &$aTimerInfo)
		{
			if(microtime(true) <= $aTimerInfo['time'])
			{
				continue;
			}
			
			call_user_func_array($aTimerInfo['call'], $aTimerInfo['args']);
			
			$aTimerInfo['time'] = (float) microtime(true) + (float) $aTimerInfo['interval'];
			
			if($aTimerInfo['repeat'] == -1)
			{
				continue;
			}
			
			--$aTimerInfo['repeat'];
			
			if($aTimerInfo['repeat'] == 0)
			{
				unset(self::$aTimers[$sTimerKey]);
			}
		}
	}
	
	
	/**
	 *	Add a timer
	 */
	static function Add($cCallback, $iInterval, $iRepeat = 1, $aArguments = array(), $pContext = null)
	{
		$sTimerKey = substr(sha1(time().uniqid()), 4, 10);
		
		# A little hack, we can presume that it's this script...
		if(is_array($cCallback) && ($cCallback[0] instanceof Script))
		{
			$cCallback[0]->aTimerScriptLocalCache[] = $sTimerKey;
		}
		
		self::$aTimers[$sTimerKey] = array
		(
			"id" => $sTimerKey,
			"call" => $cCallback,
			"interval" => (float) $iInterval,
			"repeat" => $iRepeat,
			"time" => (float) microtime(true) + (float) $iInterval,
			"args" => $aArguments,
			"context" => $pContext,
		);
		
		return $sTimerKey;
	}
	
	
	/**
	 *	Remove a timer
	 */
	static function Remove($sTimerKey)
	{
		unset(self::$aTimers[$sTimerKey]);
	}
}