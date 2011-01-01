<?php
/**
 *	OUTRAGEbot development
 */


class Debug extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		println("# Script Debug loaded, class version ".__CLASS__.".");
	}
	
	
	/**
	 *	Called when the Script is removed.
	 */
	public function onDestruct()
	{
		println("# Script Debug removed from active usage.");
	}
	
	
	/**
	 *	Called when the bot successfully connects to the network.
	 */
	public function onConnect()
	{
		println("# It looks like the Debug Script has connected!");
	}
	
	
	/**
	 *	Called when a user joins a channel.
	 */
	public function onChannelJoin($sChannel, $sNickname)
	{
		println("# {$sNickname} has joined {$sChannel}.");
	}
	
	
	/**
	 *	Called when a user parts a channel.
	 */
	public function onChannelPart($sChannel, $sNickname)
	{
		println("# {$sNickname} has left {$sChannel}.");
	}
	
	
	/**
	 *	Called when a user is kicked from a channel.
	 */
	public function onChannelKick($sChannel, $sAdminUser, $sKickedUser, $sReason)
	{
		println("# {$sKickedUser} was kicked from {$sChannel} by {$sAdmin}: {$sReason}");
	}
	
	
	/**
	 *	Called when someone changes the channel topic.
	 */
	public function onChannelTopic($sChannel, $sNickname, $sTopic)
	{
		println("# {$sNickname} set {$sChannel}'s topic to {$sTopic}");
	}
	
	
	/**
	 *	Called when someone changes their nickname.
	 */
	public function onNicknameChange($sOldNickname, $sNewNickname)
	{
		println("# {$sOldNickname} has changed their nick to {$sNewNickname}.");
	}
	
	
	/**
	 *	Called when someone quits from the network.
	 */
	public function onUserQuit($sNickname, $sReason)
	{
		println("# {$sNickname} has quit the network: {$sReason}");
	}
	
	
	/**
	 *	Called when someone sends a notice to the bot.
	 */
	public function onUserNotice($sSender, $sRecipient, $sMessage)
	{
		println("# {$sSender} sent via notice {$sRecipient} {$sMessage}");
	}
	
	
	/**
	 *	Called when someone sends a message in a channel.
	 */
	public function onChannelMessage($sChannel, $sNickname, $sMessage)
	{
		println("# {$sNickname} in {$sChannel}: {$sMessage}");
	}
	
	
	/**
	 *	Called when someone sends a command in a channel.
	 */
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
	{
		println("# {$sNickname} in {$sChannel}: command {$sCommand}, argument {$sArguments}");
	}
	
	
	/**
	 *	Called when a fellow user sends the bot a private message.
	 */
	public function onPrivateMessage($sSender, $sRecipient, $sMessage)
	{
		println("# {$sSender} sent {$sRecipient} {$sMessage}");
	}
	
	
	/**
	 *	Called on a CTCP request from a fellow user.
	 */
	public function onCTCPRequest($sNickname, $sPayload)
	{
		println("# {$sNickname} has requested '{$sPayload}'.");
	}
	
	
	/**
	 *	Called on a CTCP request from a fellow user.
	 */
	public function onCTCPResponse($sNickname, $sPayload)
	{
		println("# {$sNickname} has replied with '{$sPayload}'.");
	}
	
	
	/**
	 *	Called when there are no event handlers for this specific
	 *	numeric in OUTRAGEbot.
	 */
	public function onUnhandledEvent($pMessage)
	{
		//println("# Unhandled: {$pMessage->Raw}");
	}
}