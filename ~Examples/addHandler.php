<?php
/**
 *	Example plugin for OUTRAGEbot.
 *	This plugin explains how the binds in the bot are created
 *	and used.
 *
 *	@ignore
 *	@copyright None
 */


class Example extends Plugins
{
	public
		$sBindID,
		$aFunctions = array();
		
	
	/* Called when the plugin is loaded. */
	public function onConstruct()
	{
		/*
			-- IRC Numeric handlers --
			Because 'onInvite' is in this plugin, you don't have to
			pass argument 2 as an array.
			
			This only asks the function to call with three arguments:
				2:	$aChunks[2], or nickname.
				3:	$aChunks[3], or channel.
				
				"Westie" is a string to be passed.
			
			Values 1 and 2 (which correspond to 2 and 3 respectively) signify that
			the from the input from the server, only the second and third indices
			are selected. To get an idea which number corresponds to what, here is
			a basic INVITE command:
			
				INVITE server.host Nickname #Channel
				
				  (0)       (1)      (2)      (3)
				  
			Any invalid indicies will just return NULL values.
		*/
		$this->sBindID = $this->addHandler("Invite", "onInvite", array(2, 3, "Westie"));
		
		
		/*
			-- IRC command handlers --
			These command handlers have nothing to do with the IRC protocol, you cannot
			define what role they will have in life, what arguments they carry. You can
			however, define what function name they carry, and the command they alias
			for.
			
			Its argument list (that is used in the called function) is the same as the
			onCommand() callback, except for the absent $sCommand (argument 3). Look at
			doTime() for details.
			
			Argument 1:         'Command'        Denoting that this is a command bind.
			         2:         'doTime'         Callback (can be in a different class)
			         3:         'time'           CaSe SenSiTiVe function name.
			         
		*/
		$this->aFunctions[0] = $this->addHandler('Command', 'doTime', 'time');
		
		
		/*
			When the plugin is unloaded, the system will automatically unload, and remove
			the zombie handlers.
		*/
	}
	
	
	/* This gets called when the INVITE handler is invoked. */
	public function onInvite($sNickname, $sChannel, $sOther)
	{
		$this->Log($sNickname." has been invited to ".$sChannel.". And to prove it to you, ".$sOther." is the other!");
		// Will output: 'Username has been invited to #channel. And to prove it to you, Westie is the other!'
	}
	
	
	/* This gets called when the command handler has been invoked. */
	public function doTime($sNickname, $sChannel, $sArguments)
	{
		$this->Message($sChannel, 'Current time is: '.date(DATE_RFC822));
	}
}
