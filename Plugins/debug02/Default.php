<?php
/**
 *	Debug class for OUTRAGEbot.
 *
 *	@copyright None
 *	@package OUTRAGEbot
 */


class debug02 extends Plugins
{
	/* Variables to store the timer etc. in */
	private
		$sTimerKey,
		$sFunction;
	
	public
		$pLogLevel = 4;
		
		
	/**
	 *	This function is called when the plugin is loaded into memory.
	 */
	public function onConstruct()
	{
		$this->sTimerKey = $this->addTimer
		(
			"TimerTest",				// Function name
			3.5,					// Time period (every 3.5 seconds)
			5,					// How many times to be called
			
			'#OUTRAGEbot'				// Argument 0 to send to function
		);
		
		$this->sFunction = $this->addHandler
		(
			'Command',				// Denote it's a command handler
			'command_TestFunc',			// Callback to call when invoked
			'testing'				// Command in IRC to invoke with
		);
		
		$this->introduceFunction
		(
			"Alarm",
			"AlarmFunction"
		);
		
		$this->Log('Plugin loaded...');
	}
	
	
	/**
	 *	Function is called when the plugin is removed from memory.
	 */
	public function onDestruct()
	{
		$this->Log('Plugin unloaded...');
	}
	
	
	/**
	 *	Testing the command handler functions.
	 */
	public function command_TestFunc($sNickname, $sChannel, $sArguments)
	{
		$this->Message($sChannel, 'Hai, I say hai!');
	}
	
	
	/*
	 *	This function is called when the bot connects to the network,
	 *	which includes reconnections.
	 */
	public function onConnect()
	{
		$this->Log("plugin has detected connection to network.");
	}
	
	
	/**
	 *	Called when a user joins a channel.
	 *
	 *	@param string $sNickname Nickname
	 *	@param string $sChannel Channel
	 */
	public function onJoin($sNickname, $sChannel)
	{
		$this->Log("{$sNickname} has joined {$sChannel}");
	}
	
	
	/**
	 *	Called when a user parts a channel.
	 *
	 *	@param string $sNickname Nickname
	 *	@param string $sChannel Channel
	 *	@param string $sReason Reason for leaving channel
	 */
	public function onPart($sNickname, $sChannel, $sReason)
	{
		$this->Log("{$sNickname} has left {$sChannel}, because of '{$sReason}'");
	}
	
	
	/**
	 *	Called when a user is kicked from a channel.
	 *
	 *	@param string $sAdmin Admin that kicked the user
	 *	@param string $sNickname User that was kicked
	 *	@param string $sChannel Channel
	 *	@param string $sReason Reason for leaving channel kick
	 */
	public function onKick($sAdmin, $sKicked, $sChannel, $sReason)
	{
		$this->Log("{$sAdmin} kicked {$sKicked} from {$sChannel}, because of '{$sReason}'");
	}
	
	
	/**
	 *	Called when a user quits from the network.
	 *
	 *	@param string $sNickname User that left
	 *	@param string $sReason Reason for leaving
	 */
	public function onQuit($sNickname, $sReason)
	{
		$this->Log("{$sNickname} has left the network, because of '{$sReason}'");
	}
	
	
	/**
	 *	Called when the channel modes have been changed.
	 *	
	 *	@param string $sChannel Channel that the mode has been changed
	 *	@param string $sModes The new mode
	 */
	public function onMode($sChannel, $sModes)
	{
		$this->Log("Modes in {$sChannel} have been set to: {$sModes}");
	}
	
	
	/**
	 *	Called when a user changes their nick
	 *
	 *	@param string $sOldnick The user's old nickname
	 *	@param string $sNewnick The user's new nickname
	 */
	public function onNick($sOldnick, $sNewnick)
	{
		$this->Log("{$sOldnick} has just changed their nick to {$sNewnick}.");
	}
	

	/**
	 *	Called when someone (bot OR channel) has been noticed.
	 *
	 *	@param string $sNickname Notice sender
	 *	@param string $sChannel Channel/bot which recieved notice
	 *	@param string $sMessage Message that was sent
	 */
	public function onNotice($sNickname, $sChannel, $sMessage)
	{
		$this->Log("<notice {$sNickname} in {$sChannel}>: $sMessage");
	}
	
	
	/**
	 *	Called when someone has requested a command
	 *
	 *	@param string $sNickname Command sender
	 *	@param string $sChannel Channel which recieved command
	 *	@param string $sCommand Command name
	 *	@param string $sArguments String of arguments
	 */
	public function onCommand($sNickname, $sChannel, $sCommand, $sArguments)
	{
		$this->Log("<command {$sNickname} in {$sChannel}> {$sCommand} {$sArguments}");
		
		/* Stopping the test timer */
		if(!strcmp($sCommand, "stoptimer"))
		{
			$this->Message($sChannel, ($this->removeTimer($this->sTimerKey) ? "Timer has been killed" : "Invalid timer ID"));
			return true;
		}
	}
	
	
	/**
	 *	Called when someone sent a message
	 *
	 *	@param string $sNickname Message sender
	 *	@param string $sChannel Channel which recieved the message
	 *	@param string $sMessage Message
	 */
	public function onMessage($sNickname, $sChannel, $sMessage)
	{
		$this->Log("<privmsg {$sNickname} in {$sChannel}> {$sMessage}");
	}
	

	/**
	 *	Called when someone sent the bot a privatemessage
	 *
	 *	@param string $sNickname Message sender
	 *	@param string $sMessage Message
	 */
	public function onPrivMessage($sNickname, $sMessage)
	{
		$this->Log("<PM {$sNickname}> {$sMessage}");
	}
	
	
	/**
	 *	Called when the topic in the channel was changed.
	 *
	 *	@param string $sChannel Channel
	 *	@param string $sTopic The new topic
	 */
	public function onTopic($sChannel, $sTopic)
	{
		$this->Log("Topic in {$sChannel} has been set to {$sTopic}");
	}
	
	
	/**
	 *	This is requested every time the bot cycles through.
	 */
	public function onTick()
	{
		$this->Log("tick...");
	}
	
	
	/**
	 *	Used with the timer test, look at onConstruct
	 *
	 *	@ignore
	 */
	public function TimerTest($sChannel)
	{
		$this->Message($sChannel, 'time: '.microtime());
		//$this->Log("Timer called.");
	}
	
	
	function AlarmFunction($sChannel, $sMessage)
	{
		$this->Message($sChannel, "Timer set for 5 seconds");
		$this->addTimer(array($this, "Message"), 5, 1, $sChannel, $sMessage);
	}
}
