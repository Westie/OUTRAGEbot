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
 */


class Evaluation extends Script
{
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
	{
		if(!$this->isAdmin())
		{
			return;
		}

		if($sCommand == $this->getNetworkConfiguration("delimiter"))
		{
			ob_start();

			eval($sArguments);
			$aOutput = ob_get_contents();

			ob_end_clean();

			foreach(explode("\n", $aOutput) as $sOutput)
			{
				$sOutput = rtrim($sOutput);

				if(strlen($sOutput) < 1)
				{
					continue;
				}

				$this->Message($sChannel, $sOutput);
			}

			return END_EVENT_EXEC;
		}

		if($sCommand == "e")
		{
			ob_start();

			eval($sArguments);
			$aOutput = ob_get_contents();

			ob_end_clean();

			foreach(explode("\n", $aOutput) as $sOutput)
			{
				$sOutput = rtrim($sOutput);

				if(strlen($sOutput) < 1)
				{
					continue;
				}

				$this->Message($sChannel, $sOutput);
			}

			return END_EVENT_EXEC;
		}
	}
}
