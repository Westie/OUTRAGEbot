<?php
/**
 *	Framework class for OUTRAGEbot
 *
 *	This class allows the usage of OUTRAGEbot as a simple framework, to send
 *	a message from a forum, or as a plugin for another PHP based deamon.
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.0
 */

class Framework
{
	private
		$aStack = array();
		
		
	public function __construct($sFile)
	{
		define("BASE_DIRECTORY", realpath(__file__.'/../..'));
		require "Definitions.php";

		include "Format.php";
		include "Master.php";
		include "Socket.php";
		include "Plugins.php";
		include "Timers.php";
		include "Control.php";
		include "Configuration.php";
		
		Control::$oConfig = new ConfigParser();
		Control::$oConfig->parseConfig($sFile);
	}
	

	public function Loop(&$bVar, $mInput = array())
	{
		$this->aStack = (array) $mInput;
		
		while($bVar)
		{
	        	Timers::Scan();
			
			foreach(Control::$aBots as $oMaster)
			{
				$oMaster->Loop();
			}
			
			Timers::Scan();
			usleep(CORE_SLEEP);
		}
	}


	public function Over()
	{
		foreach(Control::$aBots as $sKey => $oMaster)
		{
			unset(Control::$aBots[$sKey]);
		}

		foreach(Control as $sKey => $mSomething)
		{
			unset(Control::$sKey);
		}
	}
}
