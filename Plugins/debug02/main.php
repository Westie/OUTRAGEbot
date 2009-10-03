<?php

/*
	Title:			Plugins/debug02 class for Happybot v2

	Description:	This is a plugin that demonstrates the power of the plugins,
					and what they can do for the bot, which is basically what
					the point of this is, to create a fully functional framework
					that can be used with or without plugins and the such.
	
	Misc notes:		A plugin! All the variable names in the functions are the
					same as used in the global callbacks.
					
	Author:			David Weston <westie [at] westie-cat.co.uk>
	
	Date/time:		Mon 02 Mar 2009 01:45:10 GMT
*/


class debug02 extends Plugins
{
	/* Variables to store the timer in */
	private
		$sTimerKey;
		
		
	/* These functions are called when the plugin is loaded or unloaded */
	public function onConstruct()
	{
	}
	
	public function onDestruct()
	{
	}
	
	
	/* This function is called when the bot connects to the network */
	public function onConnect()
	{
		$this->_("plugin has detected connection to network.");
	}
	
	
	/* Called when a user joins a channel */
	public function onJoin($sNickname, $sChannel)
	{
		$this->_("{$sNickname} has joined {$sChannel}");
	}
	
	
	/* Called when a user leaves the channel */
	public function onPart($sNickname, $sChannel, $sReason)
	{
		$this->_("{$sNickname} has left {$sChannel}, because of '{$sReason}'");
	}
	
	
	/* Called when a user is kicked from the channel */
	public function onKick($sAdmin, $sKicked, $sChannel, $sReason)
	{
		$this->_("{$sAdmin} kicked {$sKicked} from {$sChannel}, because of '{$sReason}'");
	}
	
	
	/* Called when a user quits the server */
	public function onQuit($sNickname, $sReason)
	{
		$this->_("{$sNickname} has left the network, because of '{$sReason}'");
	}
	
	
	/* Called when modes have been set. */
	public function onMode($sChannel, $sModes)
	{
		$this->_("Modes in {$sChannel} have been set to: {$sModes}");
	}
	
	
	/* Called when a user changes their nick */
	public function onNick($sOldnick, $sNewnick)
	{
		$this->_("{$sOldnick} has just changed their nick to {$sNewnick}.");
	}
	

	/* Called when someone has been noticed. */
	public function onNotice($sNickname, $sChannel, $sMessage)
	{
		$this->_("<notice {$sNickname} in {$sChannel}>: $sMessage");
	}
	
	
	/* Called when someone has requested a command */
	public function onCommand($sNickname, $sChannel, $sCommand, $sArguments)
	{
		$this->_("<command {$sNickname} in {$sChannel}> {$sCommand} {$sArguments}");
		
		/* Stopping the test timer */
		if(!strcmp($sCommand, "stoptimer"))
		{
			$this->sendMessage($sChannel, ($this->timerKill($this->sTimerKey) ? "Timer has been killed" : "Invalid timer ID"));
			return true;
		}
	}
	
	
	/* Called when someone has posted a normal message */
	public function onMessage($sNickname, $sChannel, $sMessage)
	{
		$this->_("<privmsg {$sNickname} in {$sChannel}> {$sMessage}");
	}
	

	/* Called when someone has PM'd the bot. */
	public function onPrivMessage($sNickname, $sMessage)
	{
		$this->_("<PM {$sNickname}> {$sMessage}");
	}
	
	
	/* Called when the topic has been set */
	public function onTopic($sChannel, $sTopic)
	{
		$this->_("Topic in {$sChannel} has been set to {$sTopic}");
	}
	
	
	/* This is requested every time the bot cycles through. */
	public function onTick()
	{
		$this->_("tick...");
	}
	
	
	/* Used with the timer test, look at onConstruct */
	public function TimerTest($sChannel, $sMessage)
	{
		/* Oh look! Those parameters have been passed from the timer-call function. COOL! */
		$this->sendMessage($sChannel, $sMessage);
		$this->_("Timer called.");
	}
	
	
	/* Makes life easier when debugging, I suppose */
	private function _($sString)
	{
		echo "plugin::debug02] ".trim($sString).PHP_EOL;
	}
}

?>
