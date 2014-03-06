<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		Zarthus
 *
 *	Licence:	MIT
 *
 *	TwitchLogger
 *	Log commands and chatter from twitch.tv/twitchplayspokemon
 */

 /**
 *	The directory to log to; if it does not exist the script will throw an exception
 *
 *	Either modify this to your own user, or the path you want to log to,
 *	Needs a trailing slash.
 */
define("LOG_DIRECTORY", "/home/zarthus/twitchbot/logs/");

class TwitchLogger extends Script
{
	public $commandsExecuted = 0;
	
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		if (is_dir(LOG_DIRECTORY))
		{
			echo "Loaded Twitch Logger \n";
		}
		else
		{
			throw new Exception("The directory at " . LOG_DIRECTORY . " in does not exist, did you modify it to be your user?");
		}
	}


	/**
	 *	Called when the Script is removed.
	 */
	public function onDestruct()
	{
		echo "Unloaded Twitch Logger \n";
	}


	/**
	 *	Called when the bot successfully connects to the network.
	 */
	public function onConnect()
	{
		$this->Raw("JOIN :#twitchplayspokemon");
	}

	/**
	 *	Called when someone sends a message in a channel.
	 */
	public function onChannelMessage($sChannel, $sNickname, $sMessage)
	{
		$ts = $this->getTimestamp() . ' ';
		if ( $this->isTwitchCommand($sMessage) )
		{
			$sMessage = strtolower($sMessage);
			$eMsg = addslashes(htmlentities($sMessage, ENT_QUOTES));
			$this->commandsExecuted++;
			
			file_put_contents(LOG_DIRECTORY . "cmds.log", $ts . $sNickname . '/' . $this->commandsExecuted . ': ' . $eMsg . "\r\n", FILE_APPEND);
			file_put_contents(LOG_DIRECTORY . $eMsg . ".log", $ts . $sNickname . ': ' . $eMsg . " ({$this->commandsExecuted})\r\n", FILE_APPEND);
			
		}
		else
		{
			$eMsg = addslashes(htmlentities($sMessage, ENT_QUOTES));
			
			file_put_contents(LOG_DIRECTORY . "chat.log", $ts . $sNickname . ': ' . $eMsg . "\r\n", FILE_APPEND);
		}
	}
	
	public function getTimestamp()
	{
		return '[' .  date("Y-m-d H:i:s") . ']';
	}

	/*
		Thanks to nasonfish and FiXato for the improved regular expressions
		
		# Verify the command is completely valid and does not contain invalid combinations
		/^(?i:((?<command>a|b|up|down|left|right|start|select)[2-9]?(?!\k<command>)){1,4}|democracy|anarchy)$/ 
		
		# Verify the message contains a command
		/(?i:anarchy|democracy|(?:a|b|up|down|left|right|start|select)[2-9]?)/
		
		# Alternative solution that allows to capture each command, quantifier, complete command and mode from a single regex. Far less legible though.
		# /^(?i:(?<cmd1complete>(?<cmd1>a|b|up|down|left|right|start|select)(?<cmd1quantifier>[2-9]?)(?!\k<cmd1>))(?<cmd2complete>(?<cmd2>a|b|up|down|left|right|start|select)(?<cmd2quantifier>[2-9]?))?(?!\k<cmd2>)(?<cmd3complete>(?<cmd3>a|b|up|down|left|right|start|select)(?<cmd3quantifier>[2-9]?))?(?!\k<cmd3>)(?<cmd4complete>(?<cmd4>a|b|up|down|left|right|start|select)(?<cmd4quantifier>[2-9]?))?(?!\k<cmd4>)|(?<mode>democracy|anarchy))$/ 
	*/
	
	public function isTwitchCommand($msg)
	{
		// Verify the message contains something that is in the list of available commands.
		if (preg_match('/(?i:anarchy|democracy|(?:a|b|up|down|left|right|start|select)[2-9]?)/', $msg) === 1) 
		{
			// If it is a valid command, verify it to be completely valid (and not a command that would not be processed by the real game, such as 'upup' or 'up10')
			return preg_match('/^(?i:((?<command>a|b|up|down|left|right|start|select)[2-9]?(?!\k<command>)){1,4}|democracy|anarchy)$/', $msg) === 1;
		}
		
		return false;
	}
}
