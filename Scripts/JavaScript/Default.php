<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		Jannis Pohl <mave1337@gmail.com>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     5f0b25489c21ae65471f2289c56a4475a94296dc
 *	Committed at:   Mon Sep 12 18:38:47 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class JavaScript extends Script
{
	/**
	 *	Store the V8 Engine object.
	 */
	private
		$pEngine;


	/**
	 *	Called to load the V8 engine.
	 */
	public function onConstruct()
	{
		if(!class_exists("v8js"))
		{
			return false;
		}

		$this->pEngine = new v8js("OUTRAGEbot");
		$this->pEngine->Shell = $this->internalMasterObject();

		$this->pEngine->executeString("bot = OUTRAGEbot.Shell;");
	}


	/**
	 *	Called when JS code is evaluated.
	 */
	public function onChannelMessage($sChannel, $sNickname, $sMessage)
	{
		if($this->isAdmin() && substr($sMessage, 0, 2) == "::")
		{
			$sMessage = substr($sMessage, 2);

			try
			{
				$sOutput = $this->pEngine->executeString($sMessage, "v8js-php");
			}
			catch(V8JSException $pError)
			{
				$sOutput = $pError->getMessage();
			}

			foreach(explode("\n", $sOutput) as $sLine)
			{
				$sLine = rtrim($sLine);

				if(strlen($sLine) < 1)
				{
					continue;
				}

				$sChannel($sLine);
			}

			return true;
		}

		if($this->isAdmin() && substr($sMessage, 0, 2) == "<<")
		{
			$sMessage = substr($sMessage, 2);

			try
			{
				$sOutput = "";
				$this->pEngine->executeString($sMessage, "v8js-php");
			}
			catch(V8JSException $pError)
			{
				$sOutput = $pError->getMessage();
			}

			foreach(explode("\n", $sOutput) as $sLine)
			{
				$sLine = rtrim($sLine);

				if(strlen($sLine) < 1)
				{
					continue;
				}

				$sChannel($sLine);
			}

			return true;
		}

		return false;
	}
}
