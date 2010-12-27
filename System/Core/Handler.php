<?php
/**
 *	OUTRAGEbot development
 */


class CoreHandler
{
	static function Unhandled(CoreMaster $pInstance, $pMessage)
	{
		println(" * {$pMessage->Raw}");
	}
	
	
	static function Privmsg(CoreMaster $pInstance, $pMessage)
	{
		println(" - Message : {$pMessage->Raw}");
		
		print_r($pMessage);
		
		$pInstance->triggerEvent("onChannelMessage", $pMessage->Parts[2], $pMessage->User->Nickname, $pMessage->Payload);
	}
}