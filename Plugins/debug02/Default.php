<?php
/**
 *	Debug class for OUTRAGEbot.
 *
 *	
 *	@copyright None
 *	@package OUTRAGEbot
 */



class debug02 extends Plugins
{
	/* Variables to store the timer in */
	private
		$sTimerKey;
		
		
	/* This function is called when the plugin is loaded into memory. */
	public function onConstruct()
	{
		$this->sTimerKey = $this->timerCreate
		(
			"TimerTest",				// Function name
			0.5,					// Time period (half a second)
			-1,					// To be called every half second
			
			'#OUTRAGEbot'				// Argument 0 to send to function
		);
		
		$this->Log('Plugin loaded...');
	}
	
	
	/* Function is called when the plugin is removed from memory. */
	public function onDestruct()
	{
		// Remember that the timers have to be manually killed!
		$this->timerKill($this->sTimerKey);
		$this->Log('Plugin unloaded...');
	}
	
	
	/* This function is called when the bot connects to the network */
	public function onConnect()
	{
		$this->Log("plugin has detected connection to network.");
	}
	
	
	/* Called when a user joins a channel */
	public function onJoin($sNickname, $sChannel)
	{
		$this->Log("{$sNickname} has joined {$sChannel}");
	}
	
	
	/* Called when a user leaves the channel */
	public function onPart($sNickname, $sChannel, $sReason)
	{
		$this->Log("{$sNickname} has left {$sChannel}, because of '{$sReason}'");
	}
	
	
	/* Called when a user is kicked from the channel */
	public function onKick($sAdmin, $sKicked, $sChannel, $sReason)
	{
		$this->Log("{$sAdmin} kicked {$sKicked} from {$sChannel}, because of '{$sReason}'");
	}
	
	
	/* Called when a user quits the server */
	public function onQuit($sNickname, $sReason)
	{
		$this->Log("{$sNickname} has left the network, because of '{$sReason}'");
	}
	
	
	/* Called when modes have been set. */
	public function onMode($sChannel, $sModes)
	{
		$this->Log("Modes in {$sChannel} have been set to: {$sModes}");
	}
	
	
	/* Called when a user changes their nick */
	public function onNick($sOldnick, $sNewnick)
	{
		$this->Log("{$sOldnick} has just changed their nick to {$sNewnick}.");
	}
	

	/* Called when someone has been noticed. */
	public function onNotice($sNickname, $sChannel, $sMessage)
	{
		$this->Log("<notice {$sNickname} in {$sChannel}>: $sMessage");
	}
	
	
	/* Called when someone has requested a command */
	public function onCommand($sNickname, $sChannel, $sCommand, $sArguments)
	{
		$this->Log("<command {$sNickname} in {$sChannel}> {$sCommand} {$sArguments}");
		
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
		$this->Log("<privmsg {$sNickname} in {$sChannel}> {$sMessage}");
	}
	

	/* Called when someone has PM'd the bot. */
	public function onPrivMessage($sNickname, $sMessage)
	{
		$this->Log("<PM {$sNickname}> {$sMessage}");
	}
	
	
	/* Called when the topic has been set */
	public function onTopic($sChannel, $sTopic)
	{
		$this->Log("Topic in {$sChannel} has been set to {$sTopic}");
	}
	
	
	/* This is requested every time the bot cycles through. */
	public function onTick()
	{
		$this->Log("tick...");
	}
	
	
	/* Used with the timer test, look at onConstruct */
	public function TimerTest($sChannel)
	{
		$this->sendMessage($sChannel, 'time: '.microtime());
		$this->Log("Timer called.");
	}
}
