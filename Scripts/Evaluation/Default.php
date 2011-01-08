<?php
/**
 *	OUTRAGEbot development
 */


class Evaluation extends Script
{
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
	{
		if(!$this->isAdmin())
		{
			return;
		}
		
		if($sCommand == $this->getNetworkConfiguration("delimiter"))
		{
			ob_start();
			
			eval($sArguments); 
			$aOutput = ob_get_contents(); 
			
			ob_end_clean();
		
			foreach(explode("\n", $aOutput) as $sOutput)
			{
				$sOutput = rtrim($sOutput);
				
				if(strlen($sOutput) < 1)
				{
					continue;
				}
				
				$this->Message($sChannel, $sOutput);
			}
		
			return END_EVENT_EXEC;
		}
		
		if($sCommand == "e")
		{
			ob_start();
			
			eval($sArguments); 
			$aOutput = ob_get_contents(); 
			
			ob_end_clean();
		
			foreach(explode("\n", $aOutput) as $sOutput)
			{
				$sOutput = rtrim($sOutput);
				
				if(strlen($sOutput) < 1)
				{
					continue;
				}
				
				$this->Message($sChannel, $sOutput);
			}
		
			return END_EVENT_EXEC;
		}
	}
}