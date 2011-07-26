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


/**
 *	Print a new line
 */
function println($sString)
{
	echo $sString.PHP_EOL;
}


/**
 *	Gets current memory, in kB
 */
function getMemory($sContext = "(none)")
{
	static
		$iCurrentMemory;

	println("{$sContext}: delta: ".(memory_get_usage() - $iCurrentMemory));
	$iCurrentMemory = memory_get_usage();
}


class CoreUtilities
{
	/**
	 *	Parsing and modifying the Script files.
	 *	Caution: Messy code! Needs improving!
	 */
	public static function getScriptIdentifier($sScriptName)
	{
		$sScriptLocation = ROOT."/Scripts/{$sScriptName}/Default.php";

		if(!file_exists($sScriptLocation))
		{
			return false;
		}

		if(isset(Core::$aScriptCache[$sScriptName]))
		{
			$aScriptCache = Core::$aScriptCache[$sScriptName];

			clearstatcache();

			if($aScriptCache['modifytime'] >= filemtime($sScriptLocation))
			{
				return $aScriptCache['identifier'];
			}
		}

		$sIdentifier = substr($sScriptName, 0, 8).'_'.substr(sha1(microtime()."-".uniqid()), 2, 10);
		$sClass = file_get_contents($sScriptLocation); # Ouch, this has gotta hurt.

		if(!preg_match("/class[\s]+?".$sScriptName."[\s]+?extends[\s]+?Script[\s]+?{/", $sClass))
		{
			return false;
		}

		$sClass = preg_replace("/(class[\s]+?)".$sScriptName."([\s]+?extends[\s]+?Script[\s]+?{)/", "\\1".$sIdentifier."\\2", $sClass);
		$sFile = tempnam(dirname($sScriptLocation), "vca"); # Stops the __FILE__ bugs.

		file_put_contents($sFile, $sClass);
		unset($sClass); # Weight off the shoulders anyone?

		include $sFile;
		unlink($sFile);

		Core::$aScriptCache[$sScriptName] = array
		(
			'modifytime' => filemtime($sScriptLocation),
			'identifier' => $sIdentifier,
		);

		return $sIdentifier;
	}


	/**
	 *	Internal: Function to get the date since something.
	 */
	public static function Duration($iDate1, $iDate2 = null)
	{
		if(empty($iDate2))
		{
			$iDate2 = time();
		}

   		$aDifferences = array
		(
			'seconds' => 0,
			'minutes'=> 0,
			'hours' => 0,
			'days' => 0,
			'weeks' => 0,

			'totalSeconds' => 0,
			'totalMinutes' => 0,
			'totalHours' => 0,
			'totalDays' => 0,
			'totalWeeks' => 0,
		);

		if($iDate2 > $iDate1)
		{
			$iTemp = $iDate2 - $iDate1;
		}
		else
		{
			$iTemp = $iDate1 - $iDate2;
		}

		$iSeconds = $iTemp;

		$aDifferences['weeks'] = floor($iTemp / 604800);
		$iTemp -= $aDifferences['weeks'] * 604800;

		$aDifferences['days'] = floor($iTemp / 86400);
		$iTemp -= $aDifferences['days'] * 86400;

		$aDifferences['hours'] = floor($iTemp / 3600);
		$iTemp -= $aDifferences['hours'] * 3600;

		$aDifferences['minutes'] = floor($iTemp / 60);
		$iTemp -= $aDifferences['minutes'] * 60;

		$aDifferences['seconds'] = $iTemp;

		$aDifferences['totalWeeks'] = floor($iSeconds / 604800);
		$aDifferences['totalDays'] = floor($iSeconds / 86400);
		$aDifferences['totalHours'] = floor($iSeconds / 3600);
		$aDifferences['totalMinutes'] = floor($iSeconds / 60);
		$aDifferences['totalSeconds'] = $iSeconds;

		return $aDifferences;
	}


	/**
	 *	Replaces the character with a letter in a mode string.
	 */
	public static function modeCharToLetter($sModeString)
	{
		return str_replace
		(
			array
			(
				'+',
				'%',
				'@',
				'&',
				'~',
			),

			array
			(
				'v',
				'h',
				'o',
				'a',
				'q',
			),

			$sModeString
		);
	}


	/**
	 *	Replaces the letter with a character in a mode string.
	 */
	public static function modeLetterToChar($sModeString)
	{
		return str_replace
		(
			array
			(
				'v',
				'h',
				'o',
				'a',
				'q',
			),

			array
			(
				'+',
				'%',
				'@',
				'&',
				'~',
			),

			$sModeString
		);
	}
}
