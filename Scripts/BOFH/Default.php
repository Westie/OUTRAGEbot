<?php
/**
 *	OUTRAGEbot development
 *
 */


class BOFH extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$this->addCommandHandler("bofh", function($pInstance, $sChannel, $sNickname, $sArguments)
		{
			$sOutput = file_get_contents('http://pages.cs.wisc.edu/~ballard/bofh/bofhserver.pl');
			preg_match('/<br><font size = "\+2">(.*)<\/font>/s', $sOutput, $aMatches); 

			$pInstance->Message($sChannel, trim($aMatches[1]));
			
			return END_EVENT_EXEC;
		});
	}
}