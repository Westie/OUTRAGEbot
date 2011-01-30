<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
*	Version:        <version>
 *	Git commit:     <commitHash>
 *	Committed at:   <commitTime>
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
	static function Handler(CoreMaster $pInstance, $pMessage)
	{
		foreach($pInstance->pEventHandlers as $sEventNumeric => $aEventHandlers)
		{
			if($sEventNumeric != $pMessage->Numeric)
			{
				continue;
			}
			
			$mReturn = null;
			
			foreach($aEventHandlers as $pEventHandler)
			{
				if($pEventHandler->argFormat === null)
				{
					$mReturn = self::DefaultHandler($pInstance, $pMessage, $pEventHandler);
				}
				elseif($pEventHandler->argFormat === 120)
				{
					$mReturn = self::CommandHandler($pInstance, $pMessage, $pEventHandler);
				}
				else
				{
					$mReturn = self::CustomHandler($pInstance, $pMessage, $pEventHandler);
				}
			}
			
			if($mReturn == END_EVENT_EXEC)
			{
				return true;
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
	 *	Deals with the default handlers.
	 */
	private static function DefaultHandler(CoreMaster $pInstance, $pMessage, $pEventHandler)
	{
		if(is_array($pEventHandler->callback) && ($pEventHandler->callback[0] instanceof Script))
		{
			return call_user_func($pEventHandler->callback, $pMessage);
		}
		
		return call_user_func($pEventHandler->callback, $pInstance, $pMessage);
	}
	
	
	/**
	 *	Deals with command handlers.
	 */
	private static function CommandHandler(CoreMaster $pInstance, $pMessage, $pEventHandler)
	{
		$sCommandName = $pInstance->pConfig->Network->delimiter.$pEventHandler->arguments;
		$aCommandPayload = explode(' ', $pMessage->Payload, 2);
		
		if($sCommandName != $aCommandPayload[0])
		{
			return;
		}
		
		$aCommandPayload[1] = isset($aCommandPayload[1]) ? $aCommandPayload[1] : "";
		
		if(is_array($pEventHandler->callback) && ($pEventHandler->callback[0] instanceof Script))
		{
			return call_user_func($pEventHandler->callback, $pMessage->Parts[2], $pMessage->User->Nickname, $aCommandPayload[1]);
		}
		
		return call_user_func($pEventHandler->callback, $pInstance, $pMessage->Parts[2], $pMessage->User->Nickname, $aCommandPayload[1]);
	}
	
	
	/**
	 *	Deals with the more complex custom command handlers.
	 */
	private static function CustomHandler(CoreMaster $pInstance, $pMessage, $pEventHandler)
	{
		$aArgumentList = preg_split('//', $pEventHandler->argFormat, -1, PREG_SPLIT_NO_EMPTY);
		$aArguments[] = $pInstance;
		
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
		
		return call_user_func_array($pEventHandler->callback, $aArguments);
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
