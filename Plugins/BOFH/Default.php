<?php
/**
 *      BOFH Generator example made by Westie.
 *      @ignore
 *      @copyright None
 *      @package OUTRAGEbot
 */


class BOFH extends Plugins
{
	public
		$pTitle   = "BOFH Generator",
		$pAuthor  = "Westie",
		$pVersion = "1.0";

	private
		$rHandler = "";

	
	/* Called when the plugin is loaded into memory. */
	public function onConstruct()
	{
		$this->rHandler = $this->addHandler('Command', 'getBofh', 'bofh');
	}


	/* Called when someone does ~calc (or w/e) */
	public function getBofh($sNickname, $sChannel, $sArguments)
	{
		$sOutput = file_get_contents('http://pages.cs.wisc.edu/~ballard/bofh/bofhserver.pl');
		preg_match('/<br><font size = "\+2">(.*)<\/font>/s', $sOutput, $aMatches); 
		
		$this->Message($sChannel, trim($aMatches[1]));
	}
}
