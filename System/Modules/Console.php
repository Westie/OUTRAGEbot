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


class ModuleConsole
{
	private static
		$aCommands = array(),
		$rStdin = null,
		$rStdout = null;


	/**
	 *	Called when the module is loaded.
	 */
	public static function initModule()
	{
		self::$rStdin = fopen('php://stdin', 'r');
		stream_set_blocking(self::$rStdin, 0);

		println("\r\nCAUTION: The Console module is loaded.\r\n");

		self::$aCommands = array
		(
			"help" => "consoleHelp",
			"#" => "consoleEval",
			"list-networks" => "consoleNetworkList",
			"list-errors" => "consoleErrorList",
		);
	}


	/**
	 *	Called on every tick.
	 */
	public static function onTick()
	{
		$sInput = fgets(self::$rStdin);

		if($sInput === false)
		{
			return;
		}

		$sInput = trim($sInput);

		if(!$sInput)
		{
			return self::printShell();
		}

		$aParts = explode(' ', $sInput, 2);
		$aParts[0] = strtolower($aParts[0]);

		$pConsole = (object) array
		(
			"String" => $sInput,
			"Parts" => explode(' ', $sInput),
			"Command" => $aParts[0],
			"Payload" => isset($aParts[1]) ? $aParts[1] : "",
		);

		if(isset(self::$aCommands[$aParts[0]]))
		{
			$sCall = self::$aCommands[$aParts[0]];

			self::$sCall($pConsole);
		}
		else
		{
			println("{$aParts[0]}: command not found");
		}

		return self::printShell();
	}


	/**
	 *	A little function to print the current shell.
	 */
	private static function printShell()
	{
		echo "OUTRAGEbot> ";
	}


	/**
	 *	Called when an administrator wants some help.
	 */
	private static function consoleHelp($pConsole)
	{
		println("** OUTRAGEbot console help **");
		println(" (undefined) ");
	}


	/**
	 *	Called when the administrator wants to evaluate some code.
	 */
	private static function consoleEval($pConsole)
	{
		ob_start();

		eval($pConsole->Payload);
		$sOutput = ob_get_contents();

		ob_end_clean();

		foreach((array) explode("\n", $sOutput) as $sOutputLine)
		{
			println(rtrim($sOutputLine));
		}
	}


	/**
	 *	The admin wants a list of the networks.
	 */
	private static function consoleNetworkList($pConsole)
	{
		$aInstances = Core::getListOfInstances();

		foreach($aInstances as $sInstance)
		{
			$pInstance = Core::getSpecificInstance($sInstance);

			$pServerConfig = $pInstance->getServerConfiguration();
			$pNetworkConfig = $pInstance->getNetworkConfiguration();

			println("** Instance: {$sInstance} **");
			println(" Network: {$pServerConfig->NETWORK} - {$pNetworkConfig->host}:{$pNetworkConfig->port}");
			println(" Start of connection: ".date("d/m/Y H:i:s", $pInstance->pSocket->pConfig->StartTime));
			println("");
		}
	}


	/**
	 *	The admin wants a list of those pesky errors.
	 *	I'll beautify that.
	 */
	private static function consoleErrorList($pConsole)
	{
		print_r(Core::getErrorLog());
	}
}
