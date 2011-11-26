<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     09c68fbaed58f5eaf8f1066c15fd6277f02d8812
 *	Committed at:   Sat Nov 26 19:53:03 GMT 2011
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
	public static function initClass()
	{
		self::$pFunctionList = new stdClass();

		error_reporting(E_ALL | E_STRICT);
		set_error_handler(array("Core", "errorHandler"));
	}


	/**
	 *	Called to load a module
	 */
	public static function Module($sModule)
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
	public static function LModule($sModule)
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
	public static function Library($sClass)
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
	public static function Tick()
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
	public static function Socket()
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
	public static function getCurrentInstance()
	{
		return self::$pCurrentInstance;
	}


	/**
	 *	Gets a specific CoreMaster instance.
	 */
	public static function getSpecificInstance($sInstanceName)
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
	public static function getListOfInstances()
	{
		return array_keys(self::$aInstances);
	}


	/**
	 *	Scan the configuration directory for settings.
	 */
	public static function scanConfig()
	{
		foreach(glob(ROOT.'/Configuration/*.ini') as $sDirectory)
		{
			CoreConfiguration::ParseLocation($sDirectory);
		}
	}


	/**
	 *	Adds an instance of CoreMaster to the core.
	 */
	public static function addInstance($sInstance, $pInstance)
	{
		self::$aInstances[$sInstance] = $pInstance;

		$pInstance->initiateInstance();
	}


	/**
	 *	Removes an instance of CoreMaster.
	 *	This will probably only work once I fix some 'PHP bugs'
	 */
	public static function removeInstance($sInstance)
	{
		unset(self::$aInstances[$sInstance]);
	}


	/**
	 *	Returns a suitable instance of reflection,
	 *	depending on what the method is.
	 */
	public static function invokeReflection($cCallback, $aArguments, $pInstance = null)
	{
		try
		{
			if($pInstance === null)
			{
				$pInstance = self::getCurrentInstance();
			}

			# Is this an object?
			if(is_array($cCallback))
			{
				# This will incorporate a test to check if this
				# is a PHP 5.4 Closure object.

				if(!($cCallback[0] instanceof Script))
				{
					array_unshift($aArguments, $pInstance);
				}

				$pReflection = new ReflectionMethod($cCallback[0], $cCallback[1]);

				return $pReflection->invokeArgs($cCallback[0], $aArguments);
			}

			# Sorry, but this is here for those that choose to run V8JS.
			# I should fix this anyhow.
			if($cCallback instanceof V8Function)
			{
			}

			# It's just a normal function or closure.
			array_unshift($aArguments, $pInstance);

			$pReflection = new ReflectionFunction($cCallback);

			return $pReflection->invokeArgs($aArguments);
		}
		catch(ReflectionException $pError)
		{
			return null;
		}
	}


	/**
	 *	Checks if a section of code has successfully been executed.
	 */
	public static function assert($mReturn)
	{
		return $mReturn === END_EVENT_EXEC || $mReturn === true;
	}


	/**
	 *	The main handler function. This function delegates
	 *	everything.
	 */
	public static function Handler(CoreMaster $pInstance, MessageObject $pMessage)
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
	 *	Deals with the default handlers.
	 */
	private static function DefaultHandler(CoreMaster $pInstance, MessageObject $pMessage, $pEventHandler)
	{
		return Core::invokeReflection($pEventHandler->eventCallback, array($pMessage), $pInstance);
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

		$aArguments = array
		(
			$pInstance->getChannel($pMessage->Parts[2]),
			$pInstance->getUser($pMessage->User->Nickname),
			$aCommandPayload[1],
		);

		return Core::invokeReflection($pEventHandler->eventCallback, $aArguments, $pInstance);
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

				case 'h':
				{
					$aArguments[] = $pMessage->User;
					break;
				}

				case 'u':
				{
					$aArguments[] = $pMessage->User->Nickname;
					break;
				}
			}
		}

		return Core::invokeReflection($pEventHandler->eventCallback, $aArguments, $pInstance);
	}


	/**
	 *	Adds a virtual function into the bot.
	 */
	public static function introduceFunction($aMethodNames, $cMethodCallback)
	{
		foreach((array) $aMethodNames as $sFunctionName)
		{
			$sFunctionName = strtolower($sFunctionName);

			if(!is_callable($cMethodCallback))
			{
				return false;
			}

			self::$pFunctionList->$sFunctionName = $cMethodCallback;
		}

		return true;
	}


	/**
	 *	Removes a virtual function from the bot.
	 */
	public static function removeFunction($sFunctionName)
	{
		$sFunctionName = strtolower($sFunctionName);
		unset(self::$pFunctionList->$sFunctionName);
	}


	/**
	 *	Error handler for OUTRAGEbot
	 */
	public static function errorHandler($errno, $errstr, $errfile, $errline)
	{
		self::$aErrorLog[] = (object) array
		(
			"errorCode" => $errno,
			"errorString" => $errstr,
			"offendingFile" => $errfile,
			"offendingLine" => $errline,
			"timeHandled" => time(),
		);
	}


	/**
	 *	Return (and purge if necessary) the error log.
	 */
	public static function getErrorLog($bPurge = false)
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
	 *	I don't know if this is still even needed.
	 */
	public static function getMessageObject($sString)
	{
		return new MessageObject($sString);
	}


	/**
	 *	What? Someone spelled a function call wrong? Let's protect against a crash!
	 */
	public static function __callStatic($sFunctionName, $aArguments)
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
