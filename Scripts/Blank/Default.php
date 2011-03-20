<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     4c2ddcff35192cd3ce6d7683b8b00a66dc6ab439
 *	Committed at:   Sun Mar 20 01:34:07 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 *
 *	This is obviously, the blank script.
 */


class Blank extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
	}


	/**
	 *	Called when the Script is removed.
	 */
	public function onDestruct()
	{
	}


	/**
	 *	Called when the bot successfully connects to the network.
	 */
	public function onConnect()
	{
	}


	/**
	 *	Called when the bot has disconnected from the network, for some reason.
	 */
	public function onDisconnect()
	{
	}


	/**
	 *	Called when a user joins a channel.
	 */
	public function onChannelJoin($sChannel, $sNickname)
	{
	}


	/**
	 *	Called when a user parts a channel.
	 */
	public function onChannelPart($sChannel, $sNickname)
	{
	}


	/**
	 *	Called when a user is kicked from a channel.
	 */
	public function onChannelKick($sChannel, $sAdminUser, $sKickedUser, $sReason)
	{
	}


	/**
	 *	Called when someone changes the channel topic.
	 */
	public function onChannelTopic($sChannel, $sNickname, $sTopic)
	{
	}


	/**
	 *	Called when someone changes their nickname.
	 */
	public function onNicknameChange($sOldNickname, $sNewNickname)
	{
	}


	/**
	 *	Called when someone quits from the network.
	 */
	public function onUserQuit($sNickname, $sReason)
	{
	}


	/**
	 *	Called when someone sends a notice to the bot.
	 */
	public function onUserNotice($sSender, $sRecipient, $sMessage)
	{
	}


	/**
	 *	Called when someone sends a message in a channel.
	 */
	public function onChannelMessage($sChannel, $sNickname, $sMessage)
	{
	}


	/**
	 *	Called when someone sends a command in a channel.
	 */
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
	{
	}


	/**
	 *	Called when a fellow user sends the bot a private message.
	 */
	public function onPrivateMessage($sSender, $sRecipient, $sMessage)
	{
	}


	/**
	 *	Called on a CTCP request from a fellow user.
	 */
	public function onCTCPRequest($sNickname, $sPayload)
	{
	}


	/**
	 *	Called on a CTCP request from a fellow user.
	 */
	public function onCTCPResponse($sNickname, $sPayload)
	{
	}


	/**
	 *	There's been a server error and the bot has been disconnected. Whoops!
	 *	onDisconnect is also called.
	 */
	public function onServerError($sErrorMessage)
	{
	}


	/**
	 *	Function is called when the Socket's nickname has failed to be changed,
	 *	because that nick already exists, so it's been changed to something else.
	 */
	public function onNicknameConflict($sNewNickname)
	{
	}


	/**
	 *	Called when there are no event handlers for this specific
	 *	numeric in OUTRAGEbot.
	 */
	public function onUnhandledEvent($pMessage)
	{
	}
}
