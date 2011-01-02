<?php
/**
 *	OUTRAGEbot development
 */


class Core
{
	private static
		$aModules = array(),
		$aInstances = array();
	
	
	public static
		$aScriptCache = array(),
		$pFunctionList = null;
	
	
	/**
	 *	Called when the core class is loaded.
	 */
	static function initClass()
	{
		self::$pFunctionList = new stdClass();
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
			$pInstance->Socket();
		}
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
	 *	Adds an instance of the master class to the core.
	 */
	static function addInstance($sInstance, $pInstance)
	{
		self::$aInstances[$sInstance] = $pInstance;
	}
	
	
	/**
	 *	Deals with the callback handlers
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
					$mReturn = call_user_func($cHandler, $pInstance, $pMessage);
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
					
					$mReturn = call_user_func($cHandler, $pInstance, $pMessage->User->Nickname, $aCommandPayload[0], $aCommandPayload[1]);
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
}