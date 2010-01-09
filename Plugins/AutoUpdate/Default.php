<?php
/**
 *	AutoUpdate class for OUTRAGEbot.
 *
 *	@copyright None
 *	@package OUTRAGEbot
 *	@ignore
 */


class AutoUpdate extends Plugins
{
	public
		$pTitle   = "AutoUpdate",
		$pAuthor  = "Westie",
		$pVersion = "1.0";
		
	private
		$sWebAddr = "http://outrage.typefish.co.uk/updates/";
		
	
	public function onConstruct()
	{
	}
}
