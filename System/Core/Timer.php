<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     09c68fbaed58f5eaf8f1066c15fd6277f02d8812
 *	Committed at:   Sat Nov 26 19:53:04 GMT 2011
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
		foreach(self::$aTimers as $sTimerKey => &$pTimerInfo)
		{
			if(microtime(true) <= $pTimerInfo->nextTimerCall)
			{
				continue;
			}

			Core::invokeReflection($pTimerInfo->timerCallback, $pTimerInfo->timerArguments, $pTimerInfo->timerEnvironment);

			$pTimerInfo->nextTimerCall = (float) microtime(true) + (float) $pTimerInfo->timerInterval;

			if($pTimerInfo->timerRepeat == -1)
			{
				continue;
			}

			--$pTimerInfo->timerRepeat;

			if($pTimerInfo->timerRepeat == 0)
			{
				unset(self::$aTimers[$sTimerKey]);
			}
		}
	}


	/**
	 *	Add a timer
	 */
	static function Add($cCallback, $iInterval, $iRepeat = 1, array $aArguments = array())
	{
		$pInstance = null;

		if(!is_callable($cCallback))
		{
			$pInstance = Core::getCurrentInstance();
			$cCallback = array($pInstance->pCurrentScript, $cCallback);

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
			"timerEnvironment" => $pInstance,
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
