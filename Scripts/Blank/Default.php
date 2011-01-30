<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     85afeb688f7ca5db50b99229665ff01e8cec8868
 *	Committed at:   Sun Jan 30 19:41:46 2011 +0000
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
	 *	Called when there are no event handlers for this specific
	 *	numeric in OUTRAGEbot.
	 */
	public function onUnhandledEvent($pMessage)
	{
	}
}
