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
		$sBindID;
		
	
	/* Called when the plugin is loaded. */
	public function onConstruct()
	{
		/*
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
		$this->sBindID = $this->addHandler("INVITE", "onInvite", array(2, 3, "Westie"));
	}
	
	
	/* Called when the plugin is unloaded. */
	public function onDestruct()
	{
		$this->removeHandler($this->sBindID);
	}
	
	
	/* This gets called when that bind matches. */
	function onInvite($sNickname, $sChannel, $sOther)
	{
		$this->Log($sNickname." has been invited to ".$sChannel.". And to prove it to you, ".$sOther." is the other!");
		// Will output: 'Username has been invited to #channel. And to prove it to you, Westie is the other!'
	}
}
