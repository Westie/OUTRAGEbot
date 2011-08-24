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
	}
}
