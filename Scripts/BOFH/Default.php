<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     85afeb688f7ca5db50b99229665ff01e8cec8868
 *	Committed at:   Sun Jan 30 19:41:46 2011 +0000
 *
 *	Licence:	http://www.typefish.co.uk/licences/
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
