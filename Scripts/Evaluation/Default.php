<?php
/**
 *	OUTRAGEbot development
 */


class Evaluation extends Script
{
	public function onConstruct()
	{
		println("$ Evaluation plugin loaded");
		
		$this->addEventHandler("INVITE", function($p, $m)
		{
			$p->Message("#westie", "Welcome to the goddamn jungle: {$m->Raw}");
			
			print_r($this->pEventHandlers);
		});
	}
	
	
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
	{
		if($sCommand == "help")
		{
			$sChannel("Oops, you're a fag.");
		}
	}
}