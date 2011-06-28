<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     35e8fa1395bbe0c6346ffa2e2dac4b69fed37039
 *	Committed at:   Tue Jun 28 18:00:53 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class Core
{
	private static
		$aErrorLog = array(),
		$aModules = array(),
		$aInstances = array(),
		$pCurrentInstance = null;


	public static
		$aScriptCache = array(),
		$pFunctionList = null;


	/**
	 *	Called when the core class is loaded.
	 */
	static function initClass()
	{
		self::$pFunctionList = new stdClass();

		error_reporting(E_ALL | E_STRICT);
		set_error_handler(array("Core", "errorHandler"));
	}


	/**
	 *	Called to load a module
	 */
	static function Module($sModule)
	{
		$sModuleLocation = ROOT."/System/Modules/{$sModule}.php";

		if(file_exists($sModuleLocation))
		{
			include $sModuleLocation;

			$sModuleName = "Module{$sModule}";
			self::$aModules[] = $sModuleName;

			$sModuleName::initModule();

			return true;
		}

		return false;
	}


	/**
	 *	Called to load a core module
	 */
	static function LModule($sModule)
	{
		$sModuleLocation = ROOT."/System/Core/{$sModule}.php";

		if(file_exists($sModuleLocation))
		{
			include $sModuleLocation;

			$sModuleName = "Core{$sModule}";
			self::$aModules[] = $sModuleName;

			$sModuleName::initModule();

			return true;
		}

		return false;
	}


	/**
	 *	Called to load a system class.
	 */
	static function Library($sClass)
	{
		$sClassLocation = ROOT."/System/Core/{$sClass}.php";

		if(file_exists($sClassLocation))
		{
			include $sClassLocation;
			return true;
		}

		return false;
	}


	/**
	 *	Called on every iteration, deals with global modules.
	 */
	static function Tick()
	{
		foreach(self::$aModules as $sModuleClass)
		{
			if(is_callable($sModuleClass.'::onTick'))
			{
				$sModuleClass::onTick();
			}
		}
	}


	/**
	 *	Called on every iteration, deals with the sockets.
	 */
	static function Socket()
	{
		foreach(self::$aInstances as $pInstance)
		{
			self::$pCurrentInstance = $pInstance;

			$pInstance->Socket();
		}
	}


	/**
	 *	Gets the current CoreMaster instance.
	 */
	static function getCurrentInstance()
	{
		return self::$pCurrentInstance;
	}


	/**
	 *	Gets a specific CoreMaster instance.
	 */
	static function getSpecificInstance($sInstanceName)
	{
		if(isset(self::$aInstances[$sInstanceName]))
		{
			return self::$aInstances[$sInstanceName];
		}

		return null;
	}


	/**
	 *	Return the list of CoreMaster instances.
	 */
	static function getListOfInstances()
	{
		return array_keys(self::$aInstances);
	}


	/**
	 *	Scan the configuration directory for settings.
	 */
	static function scanConfig()
	{
		foreach(glob(ROOT.'/Configuration/*.ini') as $sDirectory)
		{
			CoreConfiguration::ParseLocation($sDirectory);
		}
	}


	/**
	 *	Adds an instance of CoreMaster to the core.
	 */
	static function addInstance($sInstance, $pInstance)
	{
		self::$aInstances[$sInstance] = $pInstance;
	}


	/**
	 *	Removes an instance of CoreMaster.
	 *	This will probably only work once I fix some 'PHP bugs'
	 */
	static function removeInstance($sInstance)
	{
		unset(self::$aInstances[$sInstance]);
	}


	/**
	 *	The main handler function. This function delegates
	 *	everything.
	 */
	static function Handler(CoreMaster $pInstance, MessageObject $pMessage)
	{
		if(isset($pInstance->pEventHandlers->{$pMessage->Numeric}))
		{
			$aEventHandlers = $pInstance->pEventHandlers->{$pMessage->Numeric};

			foreach($aEventHandlers as $pEventHandler)
			{
				$mReturn = null;

				switch($pEventHandler->eventType)
				{
					case EVENT_COMMAND:
					{
						$mReturn = self::CommandHandler($pInstance, $pMessage, $pEventHandler);
						break;
					}

					case EVENT_CUSTOM:
					{
						$mReturn = self::CustomHandler($pInstance, $pMessage, $pEventHandler);
						break;
					}

					default:
					{
						$mReturn = self::DefaultHandler($pInstance, $pMessage, $pEventHandler);
						break;
					}
				}

				if(self::assert($mReturn))
				{
					return true;
				}
			}
		}

		if($pMessage->Parts[0] == "ERROR")
		{
			return CoreHandler::onServerError($pInstance, $pMessage);
		}

		$sNumeric = !is_numeric($pMessage->Numeric) ? $pMessage->Numeric : 'N'.$pMessage->Numeric;

		if(!method_exists("CoreHandler", $sNumeric))
		{
			return CoreHandler::Unhandled($pInstance, $pMessage);
		}

		return CoreHandler::$sNumeric($pInstance, $pMessage);
	}


	/**
	 *	Checks if a section of code has successfully been executed.
	 */
	public static function assert($mReturn)
	{
		return $mReturn == END_EVENT_EXEC || $mReturn == true;
	}


	/**
	 *	Checks if the event is a member of Script.
	 */
	public static function isEventScript($pEventHandler)
	{
		if(!is_array($pEventHandler->eventCallback))
		{
			return false;
		}

		if($pEventHandler->eventCallback[0] instanceof Script)
		{
			return true;
		}

		return false;
	}


	/**
	 *	Deals with the default handlers.
	 */
	private static function DefaultHandler(CoreMaster $pInstance, MessageObject $pMessage, $pEventHandler)
	{
		if(self::isEventScript($pEventHandler))
		{
			return call_user_func($pEventHandler->eventCallback, $pMessage);
		}

		return call_user_func($pEventHandler->eventCallback, $pInstance, $pMessage);
	}


	/**
	 *	Deals with command handlers.
	 */
	private static function CommandHandler(CoreMaster $pInstance, MessageObject $pMessage, $pEventHandler)
	{
		$sCommandName = $pInstance->pConfig->Network->delimiter.$pEventHandler->argumentPassed;
		$aCommandPayload = explode(' ', $pMessage->Payload, 2);

		if($sCommandName != $aCommandPayload[0])
		{
			return;
		}

		$aCommandPayload[1] = isset($aCommandPayload[1]) ? $aCommandPayload[1] : "";

		if(self::isEventScript($pEventHandler))
		{
			return call_user_func($pEventHandler->eventCallback, $pInstance->getChannel($pMessage->Parts[2]), $pMessage->User->Nickname, $aCommandPayload[1]);
		}

		return call_user_func($pEventHandler->eventCallback, $pInstance, $pInstance->getChannel($pMessage->Parts[2]), $pMessage->User->Nickname, $aCommandPayload[1]);
	}


	/**
	 *	Deals with the more complex custom command handlers.
	 */
	private static function CustomHandler(CoreMaster $pInstance, MessageObject $pMessage, $pEventHandler)
	{
		$aArgumentList = preg_split('//', $pEventHandler->argumentTypes, -1, PREG_SPLIT_NO_EMPTY);

		foreach($aArgumentList as $cArgument)
		{
			switch($cArgument)
			{
				case 'c':
				{
					$aArguments[] = $pMessage->Parts;
					break;
				}

				case 'm':
				{
					$aArguments[] = $pMessage;
					break;
				}

				case 'p':
				{
					$aArguments[] = $pMessage->Payload;
					break;
				}

				case 'r':
				{
					$aArguments[] = $pMessage->Raw;
					break;
				}

				case 'u':
				{
					$aArguments[] = $pMessage->User;
					break;
				}
			}
		}

		if(!self::isEventScript($pEventHandler))
		{
			array_unshift($aArguments, $pInstance);
		}

		return call_user_func_array($pEventHandler->eventCallback, $aArguments);
	}


	/**
	 *	Adds a virtual function into the bot.
	 */
	static function introduceFunction($sFunctionName, $cMethodCallback)
	{
		if(!is_callable($cMethodCallback))
		{
			print_r("hay");
			return false;
		}

		self::$pFunctionList->$sFunctionName = $cMethodCallback;
		return true;
	}


	/**
	 *	Removes a virtual function from the bot.
	 */
	static function removeFunction($sFunctionName)
	{
		unset(self::$pFunctionList->$sFunctionName);
	}


	/**
	 *	Error handler for OUTRAGEbot
	 */
	static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		self::$aErrorLog[] = (object) array
		(
			"errorCode" => $errno,
			"errorString" => $errstr,
			"offendingFile" => $errfile,
			"offendingLine" => $errline,
		);
	}


	/**
	 *	Return (and purge if necessary) the error log.
	 */
	static function getErrorLog($bPurge = false)
	{
		$aErrorLog = self::$aErrorLog;

		if($bPurge)
		{
			self::$aErrorLog = array();
		}

		return $aErrorLog;
	}


	/**
	 *	Parse a string from the IRC server.
	 */
	static function getMessageObject($sString)
	{
		return new MessageObject($sString);
	}


	/**
	 *	What? Someone spelled a function call wrong? Let's protect against a crash!
	 */
	static function __callStatic($sFunctionName, $aArguments)
	{
		self::$aErrorLog[] = (object) array
		(
			"errorCode" => E_WARNING,
			"errorString" => "Call to undefined function, Core library.",
			"offendingFile" => __FILE__,
			"offendingLine" => __LINE__,
		);
	}
}
