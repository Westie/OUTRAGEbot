<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     c4b0310d54d08608fa7e83818ebf75150aa23aee
 *	Committed at:   Mon Jul  4 20:50:17 BST 2011
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
		foreach(self::$aTimers as $sTimerKey => &$aTimerInfo)
		{
			if(microtime(true) <= $aTimerInfo->nextTimerCall)
			{
				continue;
			}

			call_user_func_array($aTimerInfo->timerCallback, $aTimerInfo->timerArguments);

			$aTimerInfo->nextTimerCall = (float) microtime(true) + (float) $aTimerInfo->timerInterval;

			if($aTimerInfo->timerRepeat == -1)
			{
				continue;
			}

			--$aTimerInfo->timerRepeat;

			if($aTimerInfo->timerRepeat == 0)
			{
				unset(self::$aTimers[$sTimerKey]);
			}
		}
	}


	/**
	 *	Add a timer
	 */
	static function Add($cCallback, $iInterval, $iRepeat = 1, $aArguments = array())
	{
		if(!is_callable($cCallback))
		{
			$cCallback = array(Core::getCurrentInstance()->pCurrentScript, $cCallback);

			if(!is_callable($cCallback))
			{
				return false;
			}
		}

		$sTimerKey = uniqid("vct");

		if(is_array($cCallback) && ($cCallback[0] instanceof Script))
		{
			$cCallback[0]->addLocalTimerHandler($sTimerKey);
		}

		self::$aTimers[$sTimerKey] = (object) array
		(
			"timerID" => $sTimerKey,
			"timerCallback" => $cCallback,
			"timerInterval" => (float) $iInterval,
			"timerRepeat" => $iRepeat,
			"nextTimerCall" => (float) microtime(true) + (float) $iInterval,
			"timerArguments" => $aArguments,
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
