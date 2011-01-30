<?php
/**
 *	OUTRAGEbot development
 */


class Commands extends Script
{
	private
		$pCommands = null;
	
	
	/**
	 *	Called when the Script is loaded.
	 */
	public function onConstruct()
	{
		$this->pCommands = new stdClass();
		
		$this->loadCommands();
		
		$this->addCommandHandler("cmdadd", "onAddCommand");
		$this->addCommandHandler("cmddel", "onRemoveCommand");
		$this->addEventHandler("PRIVMSG", "onMessageInput");
	}
	
	
	/**
	 *	Called when the Script is removed.
	 */
	public function onDestruct()
	{
		$this->saveCommands();
	}
	
	
	/**
	 *	Called when there's an input.
	 *	I didn't want to use three methods, when I can just use one.
	 */
	public function onMessageInput($pMessage)
	{
		/* Sort the variables out */
		$aCommandPayload = explode(' ', $pMessage->Payload, 2);
		
		$sCommandName = $aCommandPayload[0];
		$sCommandArguments = isset($aCommandPayload[1]) ? $aCommandPayload[1] : "";
		
		
		/* Check if the command exists. */
		if(!$this->doesCommandExist($sCommandName))
		{
			return;
		}
		
		ob_start();
		
		eval($this->pCommands->$sCommandName->code);
		$sOutput = ob_get_contents(); 
		
		ob_end_clean();
		
		foreach((array) explode("\n", $sOutput) as $sMessager)
		{
			$this->Message($pMessage->Parts[2], $sMessager);
		}
		
		return END_EVENT_EXEC;
	}
	
	
	/**
	 *	Add a command into the Command engine.
	 */
	public function onAddCommand($sChannel, $sNickname, $sArguments)
	{
		if(!$this->isAdmin())
		{
			return END_EVENT_EXEC;
		}
		
		if(!$sArguments)
		{
			$this->Notice($sNickname, "Usage: !cmdadd [Command Name] [PHP Evaluation Code]");
			return END_EVENT_EXEC;
		}
		
		$aArguments = explode(' ', $sArguments, 2);
		
		$sCommandName = $aArguments[0];
		$sCommandArguments = isset($aArguments[1]) ? $aArguments[1] : "";
		
		if($this->doesCommandExist($sCommandName))
		{
			$this->Notice($sNickname, "Error: That command name has already been took!");
			return END_EVENT_EXEC;
		}
		
		$this->pCommands->$sCommandName = (object) array
		(
			"command" => $sCommandName,
			"code" => $sCommandArguments,
			"channel" => null,
			"perms" => null, 
		);
		
		$this->saveCommands();
		$this->loadCommands();
		
		$this->Notice($sNickname, "Success: {$sCommandName} has been added and saved.");
		
		return END_EVENT_EXEC;
	}
	
	
	/**
	 *	Add a command into the Command engine.
	 */
	public function onRemoveCommand($sChannel, $sNickname, $sArguments)
	{
		if(!$this->isAdmin())
		{
			return END_EVENT_EXEC;
		}
		
		if(!$sArguments)
		{
			$this->Notice($sNickname, "Usage: !cmddel [Command Name]");
			return END_EVENT_EXEC;
		}
		
		$aArguments = explode(' ', $sArguments, 2);
		
		$sCommandName = $aArguments[0];
		
		if(!$this->doesCommandExist($sCommandName))
		{
			$this->Notice($sNickname, "Error: That command name doesn't exist!");
			return END_EVENT_EXEC;
		}
		
		unset($this->pCommands->$sCommandName);
		
		$this->saveCommands();
		$this->loadCommands();
		
		$this->Notice($sNickname, "Success: {$sCommandName} has been removed!");
		
		return END_EVENT_EXEC;
	}
	
	
	/**
	 *	Checks if the command is loaded into memory.
	 */
	private function doesCommandExist($sCommandName)
	{
		return isset($this->pCommands->$sCommandName) !== false;
	}
	
	
	/**
	 *	Saves the commands into files
	 */
	private function saveCommands()
	{
		foreach($this->pCommands as $pCommand)
		{
			$pResource = $this->getResource(urlencode($pCommand->command).'.txt');
			$pResource->write(serialize($pCommand));
		}
		
		return true;
	}
	
	
	/**
	 *	Loads the commands into memory
	 */
	private function loadCommands()
	{
		$this->pCommands = new stdClass();
		
		$aFileLocation = glob(ROOT."/Resources/Commands/*.txt");
		
		foreach($aFileLocation as $sFileLocation)
		{
			$sFileLocation = basename($sFileLocation);
			
			$pResource = $this->getResource($sFileLocation);
			$pCommand = unserialize($pResource->read());
			
			$this->pCommands->{$pCommand->command} = $pCommand;
		}
	}
}