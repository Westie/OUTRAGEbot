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
		$sWebAddr = "http://outrage.typefish.co.uk/_Update_/";
		
	
	public function onConstruct()
	{
		echo 'You have loaded the OUTRAGEbot AutoUpdate plugin.'.PHP_EOL;
		echo 'To verify that you would like to overwrite all System files - ';
		echo 'plugins and data are not touched, please evaluate this command:'.PHP_EOL;
		echo '$this->updateOUTRAGEbot();'.PHP_EOL;

		$this->introduceFunction('updateOUTRAGEbot', 'Download');
	}
	
	
	public function Download()
	{
		$aDownload = file($this->sWebAddr.'Download.txt');

		foreach($aDownload as $sDownload)
		{
		}
	}
}
