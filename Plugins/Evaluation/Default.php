<?php
/**
 *	Evaluation class for OUTRAGEbot.
 *
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */

class Evaluation extends Plugins
{
	public
		$pTitle   = "Evaluation",
		$pAuthor  = "Westie",
		$pVersion = "0.1a";
	
	
	/* Each of the different callbacks. */
	function onCommand($sNickname, $sChannel, $sCommand, $sArguments)
	{
		if($this->isChild())
		{
			return false;
		}		
		
		$aConfig = $this->getMasterConfig('Network');
		
		if(!strcmp($sCommand, $aConfig['delimiter']))
		{
			if(!$this->isAdmin())
			{
				return true;
			}
			
			if(!$sArguments)
			{
				$this->sendNotice($sNickname, "USAGE: {$aConfig['delimiter']}{$aConfig['delimiter']} [PHP eval code]");
				return true;
			}
			
			$aData = $this->getEval($sNickname, $sChannel, $sCommand, $sArguments);
		
			foreach((array) $aData as $sData)
			{
				$this->sendMessage($sChannel, $sData);
			}
			return true;
		}
		
		return false;
	}
	
	
	function onPrivMessage($sNickname, $sMessage)
	{
		$aData = explode(' ', $sMessage, 2);
		@list($sCommand, $sArguments) = $aData;
		unset($aData);
		
		$aConfig = $this->getMasterConfig('Network');
		
		if(!strcmp($sCommand, $aConfig['delimiter'].$aConfig['delimiter']))
		{
			if(!$this->isAdmin())
			{
				return true;
			}
			
			if(!$sArguments)
			{
				$this->sendNotice($sNickname, "USAGE: {$aConfig['delimiter']}{$aConfig['delimiter']} [PHP eval code]");
				return true;
			}
			
			$aData = $this->getEval($sNickname, $sMessage, $sCommand, $sArguments);
		
			foreach((array) $aData as $sData)
			{
				$this->sendMessage($sNickname, $sData);
			}
			return true;
		}
	}
	
	
	function getEval($sNickname, $sChannel, $sCommand, $sCode)
	{
		ob_start();
		eval($sCode); 
		$aOutput = ob_get_contents(); 
		ob_end_clean();
		
		$aOutput = explode("\n", $aOutput);
		return $aOutput;
	}
}
