<?php
/**
 *	OUTRAGEbot development
 */


/**
 *	Print a new line
 */
function println($sString)
{
	echo $sString.PHP_EOL;
}


/**
 *	Get the plugin identifier for a stored plugin.
 */
function getPluginIdentifier($sPluginName)
{
	$sDirname = ROOT."/Plugins/{$sPluginName}/Default.php";

	if(!file_exists($sDirname))
	{
		return false;
	}
	
	if(isset(Core::$aPluginCache[$sPluginName]))
	{
		$aPluginCache = Core::$aPluginCache[$sPluginName];
		
		clearstatcache();
		
		if($aPluginCache['modifytime'] >= filemtime($sDirname))
		{
			return $aPluginCache['identifier'];
		}
	}

	$sIdentifier = substr($sPluginName, 0, 8).'_'.substr(sha1(microtime()."-".uniqid()), 2, 10);
	$sClass = file_get_contents($sDirname); // Ouch, this has gotta hurt.

	if(!preg_match("/class[\s]+?".$sPluginName."[\s]+?extends[\s]+?Plugins[\s]+?{/", $sClass))
	{
		return false;
	}
	
	$sClass = preg_replace("/(class[\s]+?)".$sPluginName."([\s]+?extends[\s]+?Plugins[\s]+?{)/", "\\1".$sIdentifier."\\2", $sClass);
	$sFile = tempnam(dirname($sDirname), "nat"); // Stops the __FILE__ bugs.
	
	file_put_contents($sFile, $sClass);				
	unset($sClass); // Weight off the shoulders anyone?
	
	include $sFile;
	unlink($sFile);
	
	Core::$aPluginCache[$sPluginName] = array
	(
		'modifytime' => filemtime($sDirname),
		'identifier' => $sIdentifier,
	);
	
	return $sIdentifier;
}


/**
 *	Function to retrieve the time space between two times.
 */
function dateSince($iDate1, $iDate2 = 0)
{
	if(!$iDate2)
	{
		$iDate2 = time();
	}

	$aDifferences = array
	(
		'SECONDS' => 0,
		'MINUTES'=> 0,
		'HOURS' => 0,
		'DAYS' => 0,
		'WEEKS' => 0,
		
		'TOTAL_SECONDS' => 0,
		'TOTAL_MINUTES' => 0,
		'TOTAL_HOURS' => 0,
		'TOTAL_DAYS' => 0,
		'TOTAL_WEEKS' => 0,
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

	$aDifferences['WEEKS'] = floor($iTemp / 604800);
	$iTemp -= $aDifferences['WEEKS'] * 604800;

	$aDifferences['DAYS'] = floor($iTemp / 86400);
	$iTemp -= $aDifferences['DAYS'] * 86400;

	$aDifferences['HOURS'] = floor($iTemp / 3600);
	$iTemp -= $aDifferences['HOURS'] * 3600;

	$aDifferences['MINUTES'] = floor($iTemp / 60);
	$iTemp -= $aDifferences['MINUTES'] * 60;

	$aDifferences['SECONDS'] = $iTemp;
	
	$aDifferences['TOTAL_WEEKS'] = floor($iSeconds / 604800);
	$aDifferences['TOTAL_DAYS'] = floor($iSeconds / 86400);
	$aDifferences['TOTAL_HOURS'] = floor($iSeconds / 3600);
	$aDifferences['TOTAL_MINUTES'] = floor($iSeconds / 60);
	$aDifferences['TOTAL_SECONDS'] = $iSeconds;

	return $aDifferences;
}


/**
 *	Replaces the character with a letter in a mode string.
 */
function modeCharToLetter($sModeString)
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
function modeLetterToChar($sModeString)
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