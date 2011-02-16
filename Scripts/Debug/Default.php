<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     120646693ff8352874523a88d6a5166675cad01a
 *	Committed at:   Wed Feb 16 23:38:17 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class Debug extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		println("> Script Debug loaded, class version ".__CLASS__.".");


		/**
		 *	Here we have the custom defined arguments.
		 *
		 *	$p	Bot instance
		 *	$u	User instance		u
		 *	$c	Payload string		p
		 */
		$this->addEventHandler("INVITE", function($p, $u, $c)
		{
			$p->Message("#westie", "(custom args) OUTRAGEbot has been invited into {$c} by {$u->Nickname}");
		}, "up");


		/**
		 *	Here we use no custom arguments, just the standard ones.
		 *
		 *	$p	Bot instance
		 *	$m	Message instance
		 */
		$this->addEventHandler("INVITE", function($p, $m)
		{
			$p->Message("#westie", "(standard args) OUTRAGEbot has been invited into {$m->Payload} by {$m->User->Nickname}");
		});


		/**
		 *	A command handler.
		 *
		 *	$p	Bot instance
		 *	$c	Channel name
		 *	$n	Sender's nickname
		 *	$a	Argument string
		 */
		$this->addCommandHandler("test", function($p, $c, $n, $a)
		{
			$p->Message($c, "yes, this is a command. have fun with your testing user!");
		});
	}


	/**
	 *	Called when the Script is removed.
	 */
	public function onDestruct()
	{
		println("> Script Debug removed from active usage.");
	}


	/**
	 *	Called when the bot successfully connects to the network.
	 */
	public function onConnect()
	{
		println("> It looks like the Debug Script has connected!");
	}


	/**
	 *	Called when a user joins a channel.
	 */
	public function onChannelJoin($sChannel, $sNickname)
	{
		println("> {$sNickname} has joined {$sChannel}.");
	}


	/**
	 *	Called when a user parts a channel.
	 */
	public function onChannelPart($sChannel, $sNickname)
	{
		println("> {$sNickname} has left {$sChannel}.");
	}


	/**
	 *	Called when a user is kicked from a channel.
	 */
	public function onChannelKick($sChannel, $sAdminUser, $sKickedUser, $sReason)
	{
		println("> {$sKickedUser} was kicked from {$sChannel} by {$sAdmin}: {$sReason}");
	}


	/**
	 *	Called when someone changes the channel topic.
	 */
	public function onChannelTopic($sChannel, $sNickname, $sTopic)
	{
		println("> {$sNickname} set {$sChannel}'s topic to {$sTopic}");
	}


	/**
	 *	Called when someone changes their nickname.
	 */
	public function onNicknameChange($sOldNickname, $sNewNickname)
	{
		println("> {$sOldNickname} has changed their nick to {$sNewNickname}.");
	}


	/**
	 *	Called when someone quits from the network.
	 */
	public function onUserQuit($sNickname, $sReason)
	{
		println("> {$sNickname} has quit the network: {$sReason}");
	}


	/**
	 *	Called when someone sends a notice to the bot.
	 */
	public function onUserNotice($sSender, $sRecipient, $sMessage)
	{
		println("> {$sSender} sent via notice {$sRecipient} {$sMessage}");
	}


	/**
	 *	Called when someone sends a message in a channel.
	 */
	public function onChannelMessage($sChannel, $sNickname, $sMessage)
	{
		println("> {$sNickname} in {$sChannel}: {$sMessage}");
	}


	/**
	 *	Called when someone sends a command in a channel.
	 */
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
	{
		println("> {$sNickname} in {$sChannel}: command {$sCommand}, argument {$sArguments}");
	}


	/**
	 *	Called when a fellow user sends the bot a private message.
	 */
	public function onPrivateMessage($sSender, $sRecipient, $sMessage)
	{
		println("> {$sSender} sent {$sRecipient} {$sMessage}");
	}


	/**
	 *	Called on a CTCP request from a fellow user.
	 */
	public function onCTCPRequest($sNickname, $sPayload)
	{
		println("> {$sNickname} has requested '{$sPayload}'.");
	}


	/**
	 *	Called on a CTCP request from a fellow user.
	 */
	public function onCTCPResponse($sNickname, $sPayload)
	{
		println("> {$sNickname} has replied with '{$sPayload}'.");
	}


	/**
	 *	There's been a server error and the bot has been disconnected. Whoops!
	 */
	public function onServerError($sErrorMessage)
	{
		println("> Error message: {$sErrorMessage}");
	}


	/**
	 *	Function is called when the Socket's nickname has failed to be changed,
	 *	because that nick already exists, so it's been changed to something else.
	 */
	public function onNicknameConflict($sNewNickname)
	{
		println("> Nickname changed to {$sNewNickname} because the first choice is already in use.");
	}


	/**
	 *	Called when there are no event handlers for this specific
	 *	numeric in OUTRAGEbot.
	 */
	public function onUnhandledEvent($pMessage)
	{
		println("> Unhandled: {$pMessage->Raw}");
	}
}
