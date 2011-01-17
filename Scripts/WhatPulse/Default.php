<?php
/**
 *	OUTRAGEbot development
 *
 *	The blank script - remove callbacks one doesn't need to improve
 *	overall efficiency.
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