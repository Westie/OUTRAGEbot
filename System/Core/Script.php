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


abstract class Script extends CoreChild
{
	private
		$spScript,
		$aTimerScriptLocalCache = array(),
		$aEventScriptLocalCache = array();

	public
		$sScriptID,
		$sScriptName;


	/**
	 *	This is called when the Script is loaded.
	 */
	public final function __construct($pInstance, $sScript)
	{
		$this->sScriptName = $sScript;
		$this->spScript = $sScript;
		$this->sScriptID = __CLASS__;

		$this->internalMasterObject($pInstance);
		$this->onConstruct();
		return true;
	}


	/**
	 *	This is called when the Script is removed.
	 */
	public final function prepareRemoval()
	{
		foreach($this as $sKey => $sValue)
		{
			$this->$sKey = null;
		}

		return true;
	}


	/**
	 *	Also called, when the Script is removed.
	 */
	public final function __destruct()
	{
	}


	/**
	 *	Called when any other undefined method is called.
	 */
	public final function __call($sFunctionName, $aArgumentList)
	{
		$pInstance = $this->internalMasterObject();

		$pInstance->pCurrentScript = $this;

		if(method_exists($pInstance, $sFunctionName))
		{
			return call_user_func_array(array($pInstance, $sFunctionName), $aArgumentList);
		}
		else
		{
			if(isset(Core::$pFunctionList->$sFunctionName))
			{
				return call_user_func_array(Core::$pFunctionList->$sFunctionName, $aArgumentList);
			}
		}

		return null;
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
}
