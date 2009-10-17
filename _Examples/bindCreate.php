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
    var
        $sBindID;
        
    
    /* Called when the plugin is loaded. */
    function onConstruct()
    {
        /*
            This only asks the function to call with two arguments:
                2:    $aChunks[2], or nickname.
                3:    $aChunks[3], or channel.
        */
        $this->sBindID = $this->bindCreate("INVITE", array($this, "onInvite"), array(2, 3, "Westie"));
    }
    
    
    /* This gets called when that bind matches. */
    function onInvite($sNickname, $sChannel, $sOther)
    {
        $this->Log($sNickname." has been invited to ".$sChannel.". And to prove it to you, ".$sOther." is the other!");
		// Will output: 'Username has been invited to #channel. And to prove it to you, Westie is the other!'
    }
}
