<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     560596e0d6aba9e984a81e1b8c48c400f287116a
 *	Committed at:   Sat Feb 12 16:29:48 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


abstract class Script
{
	private
		$spScript,
		$pInstance = null;


	public
		$sScriptID,
		$sScriptName,
		$aTimerScriptLocalCache = array(),
		$aHandlerScriptLocalCache = array();


	/**
	 *	This is called when the Script is loaded.
	 */
	public final function __construct($pInstance, $sScript)
	{
		$this->pInstance = $pInstance;

		$this->sScriptName = $sScript;
		$this->spScript = $sScript;
		$this->sScriptID = __CLASS__;

		$this->onConstruct();
		return true;
	}


	/**
	 *	This is called when the Script is removed.
	 */
	public final function __destruct()
	{
		call_user_func(array($this, 'onDestruct'));

		foreach($this as $sKey => $sValue)
		{
			$this->$sKey = NULL;
		}

		return true;
	}


	/**
	 *	Called when any other undefined method is called.
	 */
	public final function __call($sFunctionName, $aArgumentList)
	{
		$this->pInstance->pCurrentScript = $this;

		if(method_exists($this->pInstance, $sFunctionName))
		{
			return call_user_func_array(array($this->pInstance, $sFunctionName), $aArgumentList);
		}
		else
		{
			if(isset(Core::$pFunctionList->$sFunctionName))
			{
				return call_user_func_array(Core::$pFunctionList->$sFunctionName, $aArgumentList);
			}
		}

		$this->pInstance->pCurrentScript = null;

		return null;
	}


	/**
	 *	Retrieve objects from the Master object.
	 */
	public final function __get($sKey)
	{
		if(property_exists($this->pInstance, $sKey))
		{
			return $this->pInstance->$sKey;
		}

		return null;
	}


	/**
	 *	Retrieves the file resource from the Resources folder.
	 */
	public function getResource($sFileString, $sMode = "w+")
	{
		return new CoreResources($this->spScript, $sFileString, $sMode);
	}


	/**
	 *	Retrieves a list of all available Resources, matching a pattern.
	 */
	public function getListOfResources($sPattern)
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
	public function isResource($sFileString)
	{
		$sResource = ROOT."/Resources/{$this->spScript}/{$sFileString}";

		return file_exists($sResource) !== false;
	}


	/**
	 *	Removes a resource from the directory.
	 */
	public function removeResource($sFileString)
	{
		$sResource = ROOT."/Resources/{$this->spScript}/{$sFileString}";

		return unlink($sResource) !== false;
	}
}
