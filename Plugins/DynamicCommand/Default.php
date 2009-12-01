<?php
/**
 *	DynamicCommand class for OUTRAGEbot.
 *
 *	@ignore
 *	@copyright None
 *	@package OUTRAGEbot
 */



class DynamicCommand extends Plugins
{
	private
		$sFilename = "",
		$aCommands = array();
		
	
	/* Called when the plugin loads */
	public function onConstruct()
	{
		$aTemp = $this->getConfig();
		
		if($aTemp !== null)
		{
			$this->sFilename = $aTemp['file'];
		}
		else
		{
			$this->sFilename = $this->oConfig->Network['name'];
		}
		
		$this->commandsLoad();
	}
	
	
	/* Called when the plugin is unloaded. */
	public function onDestruct()
	{
		$this->commandsSave();
	}

	
	/* Scan the commands as they come through */
	public function onCommand($sNickname, $sChannel, $sCommand, $sArguments)
	{
		if($this->isAdmin())
		{
			if(!strcmp($sCommand, "cmdadd"))
			{
				$aCommands = explode(' ', $sArguments, 2);
				$aCommands[0] = strtolower($aCommands[0]);
				
				$aCommands[0] = isset($aCommands[0]) ? $aCommands[0] : "";
				$aCommands[1] = isset($aCommands[1]) ? $aCommands[1] : "";
				
				if(!$aCommands[0] || !$aCommands[1])
				{
					$this->sendNotice($sNickname, "USAGE: cmdadd [Command] [Code]");
					return true;
				}
				
				if(isset($this->aCommands[$aCommands[0]]))
				{
					$this->sendNotice($sNickname, "ERROR: This function already exists.");
					return true;
				}
				
				$this->aCommands[$aCommands[0]] = array
				(
					"ChanLevel" => 0,
					"ChanUse" => "*",
					"Command" => $aCommands[1]
				);
				
				$this->sendNotice($sNickname, "SUCCESS: {$aCommands[0]} has been added.");
				$this->commandsSave();
				return true;
			}
			
			if(!strcmp($sCommand, "cmddel"))
			{
				if(!$sArguments)
				{
					$this->sendNotice($sNickname, "USAGE: cmddel [Command]");
					return true;
				}
				
				$sArguments = strtolower($sArguments);
				
				if(!isset($this->aCommands[$sArguments]))
				{
					$this->sendNotice($sNickname, "ERROR: This function doesn't exist.");
					return true;
				}
				
				unset($this->aCommands[$sArguments]);
				$this->sendNotice($sNickname, "SUCCESS: {$sArguments} has been permanently removed.");
				$this->commandsSave();
			}
			
			if(!strcmp($sCommand, "cmdget"))
			{
				if(!$sArguments)
				{
					$this->sendNotice($sNickname, "USAGE: cmdget [Command]");
					return true;
				}
				
				if(!isset($this->aCommands[$sArguments]))
				{
					$this->sendNotice($sNickname, "ERROR: This function doesn't exist.");
					return true;
				}
				
				$this->sendNotice($sNickname, Format::Bold."Command:".Format::Bold." ".$sArguments);
				$this->sendNotice($sNickname, Format::Bold."Code:   ".Format::Bold." ".$this->aCommands[$sArguments]['Command']);
				
				if($this->aCommands[$sArguments]['ChanUse'] != '*')
				{
					$this->sendNotice($sNickname, Format::Bold."Channel:".Format::Bold." ".$this->aCommands[$sArguments]['ChanUse']);
				}
				if($this->aCommands[$sArguments]['ChanLevel'] != '0')
				{
					$this->sendNotice($sNickname, Format::Bold."Level:  ".Format::Bold." ".$this->aCommands[$sArguments]['ChanUse']);
				}
			}
			
			if(!strcmp($sCommand, "cmdset"))
			{		
				$aCommands = explode(' ', $sArguments, 3);
				$aCommands[0] = strtolower($aCommands[0]);
				
				$aCommands[0] = isset($aCommands[0]) ? $aCommands[0] : "";
				$aCommands[1] = isset($aCommands[1]) ? $aCommands[1] : "";
				$aCommands[2] = isset($aCommands[2]) ? $aCommands[2] : "";
				
				if($aCommands[0] === false || $aCommands[1] === false || $aCommands[2] === false)
				{
					$this->sendNotice($sNickname, "USAGE: cmdset [Command] [Key] [Value]");
					return true;
				}
				
				if(!isset($this->aCommands[$aCommands[0]]))
				{
					$this->sendNotice($sNickname, "ERROR: This function doesn't exist.");
					return true;
				}
				
				$this->aCommands[$aCommands[0]][$aCommands[1]] = $aCommands[2];
				
				$this->sendNotice($sNickname, "SUCCESS: {$aCommands[0]} has been updated.");
				$this->commandsSave();
				return true;
			}
			
			if(!strcmp($sCommand, "cmdhelp"))
			{
				$this->sendNotice($sNickname, "cmdadd [Command] [Code]");
				$this->sendNotice($sNickname, "cmddel [Command]");
				$this->sendNotice($sNickname, "cmdset [Command] [Key] [Value]");
				$this->sendNotice($sNickname, "cmdget [Command]");
				return true;
			}
		}
			
		/* Deal with the rest */
		$this->commandsScan($sNickname, $sChannel, $this->oConfig->Network['delimiter'].$sCommand, $sArguments);
		return false;
	}
	
	
	/* Same as above, but for messages. */
	public function onMessage($sNickname, $sChannel, $sMessage)
	{
		$aCommands = explode(' ', $sMessage, 2);
		$aCommands[0] = isset($aCommands[0]) ? $aCommands[0] : "";
		$aCommands[1] = isset($aCommands[1]) ? $aCommands[1] : "";
		
		$this->commandsScan($sNickname, $sChannel, $aCommands[0], $aCommands[1]);
	}
	
	
	/* Load the commands into memory */
	public function commandsLoad()
	{
		$sFile = dirname(__FILE__).'/commands/'.$this->sFilename.'.txt';
		if(file_exists($sFile))
		{
			$sText = file_get_contents($sFile);
			$this->aCommands = unserialize($sText);
		}
		return true;
	}
	
	
	/* Save the commands to hard disk. */
	public function commandsSave()
	{
		$sText = serialize($this->aCommands);
		$sFile = dirname(__FILE__).'/commands/'.$this->sFilename.'.txt';
		file_put_contents($sFile, $sText);
		return true;
	}
	
	
	/* Scan through the commands */
	public function commandsScan($sNickname, $sChannel, $sCommand, $sArguments)
	{		
		if(isset($this->aCommands[$sCommand]))
		{
			$aCommand = $this->aCommands[$sCommand];
			
			if($aCommand['ChanUse'] !== "*")
			{
				if($sChannel != $aCommand['ChanUse'])
				{
					return false;
				}
			}
			
			if($aCommand['ChanLevel'] != 0)
			{
				switch($aCommand['ChanLevel'])
				{
					case 1:
					{
						if(!$this->isUserVoice($sChannel, $sNickname))
						{
							return false;
						}
						break;
					}
					case 2:
					{
						if(!$this->isUserHalfOp($sChannel, $sNickname))
						{
							return false;
						}
						break;
					}
					case 3:
					{
						if(!$this->isUserOper($sChannel, $sNickname))
						{
							return false;
						}
						break;
					}
					case 4:
					{
						if(!$this->isUserAdmin($sChannel, $sNickname))
						{
							return false;
						}
						break;
					}
					case 5:
					{
						if(!$this->isUserOwner($sChannel, $sNickname))
						{
							return false;
						}
						break;
					}
				}
			}

			ob_start();
			eval($aCommand['Command']);
			$sOutput = ob_get_contents(); 
			ob_end_clean();
			
			foreach((array) explode("\n", $sOutput) as $sMessager)
			{
				$this->Message($sChannel, $sMessager);
			}		
			return true;
		}
		return false;
	}
}
