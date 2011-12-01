<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Beta

 *	Git commit:     b882ae6528fa3950a03f50ec895ea670f8541f26
 *	Committed at:   Thu Dec  1 22:35:20 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class ModuleDebug
{
	private static
 		$aFunctionList = array();


 	public static function initModule()
 	{
 		println(" * Debug module loaded");
 	}


 	public static function onTick()
 	{
 		$aBacktraces = debug_backtrace(false);

		foreach($aBacktraces as $aBacktrace)
		{
			$sString = "";

			if(isset($aBacktrace['class']))
			{
				$sString = "{$aBacktrace['class']}::";
			}

			$sString .= "{$aBacktrace['function']}";

			if(!isset(self::$aFunctionList[$sString]))
			{
				self::$aFunctionList[$sString] = 0;
			}

			++self::$aFunctionList[$sString];
		}
 	}


 	public static function Output()
 	{
 		$sString = "";

 		foreach(self::$aFunctionList as $sFunctionName => $iCalledCount)
		{
			$sString .= "{$sFunctionName} => {$iCalledCount}".PHP_EOL;
		}

		println(ROOT."/Output.txt");
		file_put_contents(ROOT."/Output.txt", $sString);
		return true;
 	}
 }
