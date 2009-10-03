<?php


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
		
		if(!strcmp($sCommand, $this->oBot->oConfig->Network['delimiter']))
		{
			if(!$this->isAdmin())
			{
				return true;
			}
			
			if(!$sArguments)
			{
				$this->sendNotice($sNickname, "USAGE: {$this->oBot->oConfig->Network['delimiter']}{$this->oBot->oConfig->Network['delimiter']} [PHP eval code]");
				return true;
			}
			
			$aData = $this->getEval($sNickname, $sChannel, $sCommand, $sArguments);
		
			foreach((array) $aData as $sData)
			{
				$this->sendMessage($sChannel, trim($sData));
			}
			return true;
		}
		
		return false;
	}
	
	
	function onPrivMessage($sNickname, $sMessage)
	{
		$aData = explode(' ', $sMessage, 2);
		list($sCommand, $sArguments) = $aData;
		unset($aData);
		
		if(!strcmp($sCommand, $this->oBot->oConfig->Network['delimiter'].$this->oBot->oConfig->Network['delimiter']))
		{
			if(!$this->isAdmin())
			{
				return true;
			}
			
			if(!$sArguments)
			{
				$this->sendNotice($sNickname, "USAGE: {$this->oBot->oConfig->Network['delimiter']}{$this->oBot->oConfig->Network['delimiter']} [PHP eval code]");
				return true;
			}
			
			$aData = $this->getEval($sNickname, $sMessage, $sCommand, $sArguments);
		
			foreach((array) $aData as $sData)
			{
				$this->sendMessage($sNickname, trim($sData));
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
