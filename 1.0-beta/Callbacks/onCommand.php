<?php

if($this->isAdmin())
{
	if(!strcmp($sCommand, "action"))
	{
		if(!$sArguments)
		{
			$this->Notice($sNickname, "USAGE: action [Message]");
			return true;
		}
		
		$this->Action($sChannel, $sArguments);
		return true;
	}
	
	if(!strcmp($sCommand, "talkto"))
	{
		$aData = explode(' ', $sArguments, 2);
		
		if(!$aData[0] || !$aData[1])
		{
			$this->Notice($sNickname, "USAGE: talkto [Channel] [Message]");
			return true;
		}
		
		$this->Message($aData[0], $aData[1]);
		return true;
	}
	
	if(!strcmp($sCommand, "raw"))
	{
		if(!$sArguments)
		{
			$this->Notice($sNickname, "USAGE: raw {all} [IRC Raw]");
			return true;
		}
		
		if(!strcasecmp(substr($sArguments, 0, 3), "all"))
		{
			$this->Send(substr($sArguments, 4), true);
		}
		else
		{
			$this->oCurrentBot->Output($sArguments);
		}
		return true;
	}
	
	if(!strcmp($sCommand, "join"))
	{
		if(!$sArguments)
		{
			$this->Notice($sNickname, "USAGE: join {all} [Channel]");
			return true;
		}
		
		if(!strcasecmp(substr($sArguments, 0, 3), "all"))
		{
			$sArguments = substr($sArguments, 4);
			$this->Send("JOIN {$sArguments}", true);
		}
		else
		{
			$this->oCurrentBot->Output("JOIN {$sArguments}");
		}
		return true;
	}
	
	if(!strcmp($sCommand, "leave"))
	{
		if(!$sArguments)
		{
			$this->Notice($sNickname, "USAGE: leave {all} [Channel]");
			return true;
		}
		
		if(!strcasecmp(substr($sArguments, 0, 3), "all"))
		{
			$sArguments = substr($sArguments, 4);
			$this->Send("PART {$sArguments}", true);
		}
		else
		{
			$this->oCurrentBot->Output("PART {$sArguments}");
		}
		return true;
	}
}

?>