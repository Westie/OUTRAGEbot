<?php
/**
 *	Globals class for OUTRAGEbot
 *
 *	Globals are a rather neat feature, if you don't want to use plugins. Yet in this version they are bulky,
 *	and will possibly need some work to make them more useful.
 *
 *	@ignore
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2009 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.0
 */


/* Just checking if we have stuff included. */
if(!defined("GLOBALS_INCLUDED"))
{
	return;
}


/* If so, let's load the class! */
class Globals
{
	private
		$oMaster = null,
		$aModules = array();
		
	
	/* Called when the globals are initiated */
	public function __construct()
	{
		$this->globalLoad();
	}
	
	
	/* Calling functions from the main bot */
	private function __call($sFunction, $aArguments)
	{
		if($sFunction[0] == '_')
		{
			return;
		}
		
		$aFunction = array($this->oMaster, $sFunction);
		
		if(is_callable($aFunction))
		{
			return call_user_func_array($aFunction, $aArguments);
		}
	}
	
	
	/* Getting variables from the main bot. */
	private function __get($sKey)
	{
		if(isset($this->oMaster->$sKey))
		{
			return $this->oMaster->$sKey;
		}
		else
		{
			return null;
		}
	}
	
	
	/* Load a global event */
	public function globalLoad($sGlobal = false)
	{
		/* Hurrah for a long function to save calls */
		foreach((array) glob(BASE_DIRECTORY.'/Callbacks/'.($sGlobal === false ? '*' : $sGlobal).'.php') as $sGlob)
		{
			$sContents = str_replace(array('<?php', '<?', '?>'), "", file_get_contents($sGlob));
			$this->aModules[substr(basename($sGlob), 0, -4)] = $sContents;
		}
		
		return true;
	}
	
	
	/* Invoke an event */
	public function globalInvoke(Master $oMaster, $sModule, $aArguments)
	{
		$this->oMaster = &$oMaster;
		call_user_func_array(array($this, '_'.$sModule), $aArguments);
		return true;
	}
	
	
	/* Called when a user joins a channel. */
	private function _onJoin($sNickname, $sChannel)
	{
		eval($this->aModules['onJoin']);
	}
	
	
	/* Called when a user leaves the channel */
	private function _onPart($sNickname, $sChannel, $sReason)
	{
		eval($this->aModules['onPart']);
	}
	
	
	/* Called when a user quits the server */
	private function _onQuit($sNickname, $sReason)
	{
		eval($this->aModules['onQuit']);
	}
	
	
	/* Called when modes have been set. */
	private function _onMode($sChannel, $sModes)
	{
		eval($this->aModules['onMode']);
	}
	
	
	/* Called when a user changes their nick */
	private function _onNick($sOldnick, $sNewnick)
	{
		eval($this->aModules['onNick']);
	}
	

	/* Called when someone has been noticed. */
	private function _onNotice($sNickname, $sChannel, $sMessage)
	{
		eval($this->aModules['onNotice']);
	}
	
	
	/* Called when someone has requested a command */
	private function _onCommand($sNickname, $sChannel, $sCommand, $sArguments)
	{
		eval($this->aModules['onCommand']);
	}
	
	
	/* Called when someone has posted a normal message */
	private function _onMessage($sNickname, $sChannel, $sMessage)
	{
		eval($this->aModules['onMessage']);
	}
	

	/* Called when someone has PM'd the bot. */
	private function _onPrivMessage($sNickname, $sMessage)
	{
		eval($this->aModules['onPrivMessage']);
	}
	
	
	/* Called when the topic has been set */
	private function _onTopic($sChannel, $sTopic)
	{
		eval($this->aModules['onTopic']);
	}
}