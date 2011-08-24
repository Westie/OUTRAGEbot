<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     0638fa8bb13e1aca64885a4be9e6b7d78aab0af7
 *	Committed at:   Wed Aug 24 23:16:56 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class Commands extends Script
{
	/**
	 *	A variable for storing things.
	 */
	private
		$aCommands = array();


	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$this->addCommandHandler("acommand", "addCommand");
		$this->addEventHandler("PRIVMSG", "processCommand");

		$this->introduceCommands();
	}


	/**
	 *	Add a command into memory.
	 */
	public function addCommand($sChannel, $sNickname, $sArguments)
	{
		if(!$this->isAdmin())
		{
			return END_EVENT_EXEC;
		}

		if(!$sArguments)
		{
			$this->Notice($sNickname, "Error: acommand [commandName] [PHP code]");
			return END_EVENT_EXEC;
		}

		list($sCommand, $sCode) = explode(' ', $sArguments, 2);

		$sCommand = urlencode($sCommand);

		$pResource = $this->getResource("{$sCommand}.txt");
		$pResource->write($sCode);

		$this->introduceCommands();

		$this->Notice($sNickname, "Successfully enabled {$sCommand} with: { {$sCode} }");
		return END_EVENT_EXEC;
	}


	/**
	 *	Load the commands into memory
	 */
	private function introduceCommands()
	{
		$this->aCommands = array();
		$aResources = $this->getListOfResources("*.txt");

		foreach($aResources as $sResource)
		{
			$sCommand = urldecode($sResource);
			$sCommand = substr($sCommand, 0, -4);

			$pResource = $this->getResource($sResource);

			$this->aCommands[$sCommand] = $pResource->read();
		}
	}


	/**
	 *	Magic stuff! We can call functions now!
	 */
	public function processCommand($pMessage)
	{
		$sChannel = $this->getChannel($pMessage->Parts[2]);
		$sNickname = $pMessage->User->Nickname;
		$sCommand = substr($pMessage->Parts[3], 1);
		$sArguments = substr($pMessage->Payload, strlen($pMessage->Parts[3]));

		if(!isset($this->aCommands[$sCommand]))
		{
			return false;
		}

		foreach(explode("\n", CoreUtilities::Evaluate($sArguments) as $sMessager)
		{
			$this->Message($pMessage->Parts[2], $sMessager);
		}

		return END_EVENT_EXEC;
	}
}
