<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     5d1624676fbbbfec531270ed6ef862070be017c2
 *	Committed at:   Sun Dec 11 12:42:11 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


abstract class Script extends CoreChild
{
	private
		$spScript,
		$aVariableLocalCache = array(),
		$aTimerScriptLocalCache = array(),
		$aEventScriptLocalCache = array(),
		$bMarkedAsRemoved = false;
	
	public
		$sScriptID,
		$sScriptName,
		$tMessageQueue; // Timer

		
	/**
	 *	This is called when the Script is loaded.
	 */
	public final function __construct($pInstance, $sScript)
	{
		$this->sScriptName = $sScript;
		$this->spScript = $sScript;
		$this->sScriptID = __CLASS__;

		$this->internalMasterObject($pInstance);

		$this->wakeupScript();
		$this->onConstruct();
		
		ini_set('display_errors', 1);
		
		return true;
	}

	public final function messageQueue($verbose = false)
	{
		$stmt = $this->dbh->query('SELECT `message`, `destination` FROM `message_queue` WHERE `executed` = 0');
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($verbose) $this->Message($this->MainBotChannel(), '{c:orange}' . count($res), FORMAT);
		if (count($res) != 0)
		{
			foreach ($res as $msg) 
			{
				$this->Message($msg['destination'], $msg['message'], FORMAT);
				if ($verbose) $this->Message($this->MainBotChannel(), '{c:red}Sending '.  $msg['message'] . ' to ' . $msg['destination'], FORMAT);
			}
			
			$this->Message($this->MainBotChannel(), 'Sent {c:orange}{b}' . count($res) . '{r} message' . (count($res) == 1 ? '' : 's') . ' from the message queue to their destination.', FORMAT);
			
			$this->dbh->query('UPDATE `message_queue` SET `executions_left` = executions_left - 1 WHERE `executions_left` > 0');
			$this->dbh->query('UPDATE `message_queue` SET `executed` = 1 WHERE `executions` = 0;');
		}
	}

	/**
	 *	This is called when the Script is removed.
	 */
	public final function prepareRemoval()
	{
		if($this->bMarkedAsRemoved)
		{
			return true;
		}

		$this->serialiseScript();

		foreach($this as $sKey => $sValue)
		{
			$this->$sKey = null;
		}

		return $bMarkedAsRemoved = true;
	}


	/**
	 *	Also called, when the Script is removed.
	 */
	public final function __destruct()
	{
		if(!$bMarkedAsRemoved)
		{
			$this->prepareRemoval();
		}
	}


	/**
	 *	Called when any other undefined method is called.
	 */
	public final function __call($sMethod, $aArguments)
	{
		$pInstance = $this->internalMasterObject();
		$pInstance->pCurrentScript = $this;

		try
		{
			$pReflection = new ReflectionMethod($pInstance, $sMethod);

			return $pReflection->invokeArgs($pInstance, $aArguments);
		}
		catch(ReflectionException $pError)
		{
			try
			{
				$sMethod = strtolower($sMethod);

				if(!isset(Core::$pFunctionList->$sMethod))
				{
					return null;
				}

				$cStaticMethod = Core::$pFunctionList->$sMethod;

				$pReflection = new ReflectionMethod($cStaticMethod[0], $cStaticMethod[1]);
				return $pReflection->invokeArgs($pInstance, $aArguments);
			}
			catch(ReflectionException $pError)
			{
				return null;
			}
		}
	}


	/**
	 *	Sets the internal variable cache.
	 */
	public final function __set($sKey, $mValue)
	{
		$this->aVariableLocalCache[$sKey] = $mValue;
	}


	/**
	 *	Retrieve objects from the Master object.
	 */
	public final function __get($sKey)
	{
		$pInstance = $this->internalMasterObject();

		if(property_exists($pInstance, $sKey))
		{
			return $pInstance->$sKey;
		}
		elseif(isset($this->aVariableLocalCache[$sKey]))
		{
			return $this->aVariableLocalCache[$sKey];
		}

		return null;
	}


	/**
	 *	Retrieves the file resource from the Resources folder.
	 */
	public final function getResource($sFileString, $sMode = "w+")
	{
		return new CoreResource($this->spScript, $sFileString, $sMode);
	}


	/**
	 *	Retrieves a list of all available Resources, matching a pattern.
	 */
	public final function getListOfResources($sPattern)
	{
		$sCurrentDirectory = getcwd();

		chdir(ROOT."/Resources/{$this->spScript}/");
		$aMatches = glob($sPattern);

		chdir($sCurrentDirectory);
		return $aMatches;
	}


	/**
	 *	Checks if a resource exists or not.
	 */
	public final function isResource($sFileString)
	{
		$sResource = ROOT."/Resources/{$this->spScript}/{$sFileString}";

		return file_exists($sResource) !== false;
	}


	/**
	 *	Removes a resource from the directory.
	 */
	public final function removeResource($sFileString)
	{
		$sResource = ROOT."/Resources/{$this->spScript}/{$sFileString}";

		return unlink($sResource) !== false;
	}


	/**
	 *	Add a timer handler to the local cache.
	 */
	public final function addLocalTimerHandler($sHandler)
	{
		$this->aTimerScriptLocalCache[] = $sHandler;
	}


	/**
	 *	Returns a list of timer handlers.
	 */
	public final function getLocalTimerHandlers()
	{
		return $this->aTimerScriptLocalCache;
	}


	/**
	 *	Add an event handler to the local cache.
	 */
	public final function addLocalEventHandler($sHandler)
	{
		$this->aEventScriptLocalCache[] = $sHandler;
	}


	/**
	 *	Returns a list of timer handlers.
	 */
	public final function getLocalEventHandlers()
	{
		return $this->aEventScriptLocalCache;
	}


	/**
	 *	Called to serialise this object.
	 */
	private final function serialiseScript()
	{
		$sSerialisation = serialize($this->aVariableLocalCache);

		$pResource = $this->getModuleSerialisationResource();
		$pResource->write($sSerialisation);
	}


	/**
	 *	Called to wake up the object.
	 */
	private final function wakeupScript()
	{
		$pResource = $this->getModuleSerialisationResource();
		$this->aVariableLocalCache = unserialize($pResource->read());
	}


	/**
	 *	This method returns a CoreResource of a location to
	 *	serialise a network environment to.
	 */
	private final function getModuleSerialisationResource()
	{
		$sNetwork = $this->getNetworkConfiguration("name");

		return new CoreResource("ScriptEnvironment", "/{$this->spScript}/{$sNetwork}.object", "w+");
	}
}
