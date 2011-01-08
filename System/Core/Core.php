<?php
/**
 *	OUTRAGEbot development
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
	 *	Deals with the callback handlers
	 *
	 *	I need to clean this up
	 */
	static function Handler(CoreMaster $pInstance, $pMessage)
	{
		foreach($pInstance->pEventHandlers as $sEvent => $aEventHandler)
		{
			if($sEvent != $pMessage->Numeric)
			{
				continue;
			}
			
			foreach($aEventHandler as $aHandler)
			{
				list($cHandler, $sArgumentList) = $aHandler;
				
				$mReturn = 0;
				$aArguments = array($pInstance);
				
				if($sArgumentList === null)
				{
					if(is_array($cHandler) && ($cHandler[0] instanceof Script))
					{
						$mReturn = call_user_func($cHandler, $pMessage);
					}
					else
					{
						$mReturn = call_user_func($cHandler, $pInstance, $pMessage);
					}
				}
				elseif(ord(substr($sArgumentList, 0, 1)) === 0xFF)
				{
					$sCommandName = $pInstance->pConfig->Network->delimiter.substr($sArgumentList, 1);
					$aCommandPayload = explode(' ', $pMessage->Payload, 2);
					
					if($sCommandName != $aCommandPayload[0])
					{
						continue;
					}
					
					if(!isset($aCommandPayload[1]))
					{
						$aCommandPayload[1] = "";
					}
					
					if(is_array($cHandler) && ($cHandler[0] instanceof Script))
					{
						$mReturn = call_user_func($cHandler, $pMessage->Parts[2], $pMessage->User->Nickname, $aCommandPayload[0], $aCommandPayload[1]);
					}
					else
					{
						$mReturn = call_user_func($cHandler, $pInstance, $pMessage->Parts[2], $pMessage->User->Nickname, $aCommandPayload[0], $aCommandPayload[1]);
					}
				}
				else
				{										
					foreach(preg_split('//', $sArgumentList, -1, PREG_SPLIT_NO_EMPTY) as $cArgument)
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
					
					$mReturn = call_user_func_array($cHandler, $aArguments);
				}
			
				if($mReturn == END_EVENT_EXEC)
				{
					return true;
				}
			}
		}
		
		$sNumeric = !is_numeric($pMessage->Numeric) ? $pMessage->Numeric : 'N'.$pMessage->Numeric;
		
		if(!method_exists("CoreHandler", $sNumeric))
		{
			return CoreHandler::Unhandled($pInstance, $pMessage);
		}
		
		return CoreHandler::$sNumeric($pInstance, $pMessage);
	}
	
	
	/**
	 *	Adds a virtual function into the bot.
	 */
	static function introduceFunction($sFunctionName, $cMethodCallback)
	{
		if(!is_callable($cMethodCallback))
		{
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
			"number" => $errno,
			"string" => $errstr,
			"file" => $errfile,
			"line" => $errline,
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
		$pMessage = new stdClass();
		
		$pMessage->Raw = $sString;
		$pMessage->Parts = explode(' ', $sString);
		$pMessage->Numeric = $pMessage->Parts[1];
		$pMessage->User = CoreMaster::parseHostmask(substr($pMessage->Parts[0], 1));
		$pMessage->Payload = (($iPosition = strpos($sString, ':', 2)) !== false) ? substr($sString, $iPosition + 1) : '';
		
		return $pMessage;
	}
	
	
	/**
	 *	What? Someone spelled a function call wrong? Let's protect against a crash!
	 */
	static function __callStatic($sFunctionName, $aArguments)
	{
		self::$aErrorLog[] = (object) array
		(
			"number" => E_WARNING,
			"string" => "Call to undefined function, Core library.",
			"file" => "",
			"line" => "",
		);
	}
}