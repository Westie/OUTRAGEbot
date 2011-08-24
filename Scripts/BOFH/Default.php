<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     0638fa8bb13e1aca64885a4be9e6b7d78aab0af7
 *	Committed at:   Wed Aug 24 23:16:55 BST 2011
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
