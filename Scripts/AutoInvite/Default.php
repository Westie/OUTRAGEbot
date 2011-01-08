<?php
/**
 *	OUTRAGEbot development
 *
 */


class AutoInvite extends Script
{
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$this->addEventHandler("INVITE", "onInvite");
	}
	
	
	/**
	 *	Custom callback: Invite to a channel
	 */
	public function onInvite($pMessage)
	{
		$sChannel = $pMessage->Payload;
		
		$this->Join($sChannel);
	}
}