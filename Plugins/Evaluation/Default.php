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
		
		$sDelimiter = $this->getNetworkConfig('delimiter');
		
		if(!strcmp($sCommand, $sDelimiter))
		{
			if(!$this->isAdmin())
			{
				return true;
			}
			
			if(!$sArguments)
			{
				$this->sendNotice($sNickname, "USAGE: {$sDelimiter}{$sDelimiter} [PHP eval code]");
				return true;
			}
			
			$aData = $this->getEval($sNickname, $sChannel, $sCommand, $sArguments);
		
			foreach((array) $aData as $sData)
			{
				$this->sendMessage($sChannel, $sData);
			}
			return true;
		}
		
		
		if(!strcmp($sCommand, 'exe'))
		{
			if(!$this->isAdmin())
			{
				return true;
			}
			
			if(!$sArguments)
			{
				$this->sendNotice($sNickname, "USAGE: {$sDelimiter}exe [PHP eval code]");
				return true;
			}
			
			exec($sArguments, $aData);
		
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
		
		$sDelimiter = $this->getNetworkConfig('delimiter');
		
		if(!strcmp($sCommand, $sDelimiter.$sDelimiter))
		{
			if(!$this->isAdmin())
			{
				return true;
			}
			
			if(!$sArguments)
			{
				$this->sendNotice($sNickname, "USAGE: {$sDelimiter}{$sDelimiter} [PHP eval code]");
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
