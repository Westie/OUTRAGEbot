<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     b4261585b7804e8c46a15f36d4cb274a811f0586
 *	Committed at:   Mon Aug 29 23:47:32 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class Evaluation extends Script
{
	/**
	 *	Store the tokeniser class.
	 */
	private
		$pTokeniser;


	/**
	 *	Called when the class is constructed.
	 */
	public function onConstruct()
	{
		if(!class_exists("Tokeniser"))
		{
			include "Tokeniser.php";
		}

		$this->pTokeniser = new Tokeniser();
	}


	/**
	 *	Called whenever there's a command issued in the channel.
	 */
	public function onChannelCommand($sChannel, $sNickname, $sCommand, $sArguments)
	{
		if(!$this->isAdmin())
		{
			return;
		}

		if($sCommand == $this->getNetworkConfiguration("delimiter"))
		{
			$this->pTokeniser->Analyse($sArguments);
			$sArguments = $this->pTokeniser->getOutput();

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


	/**
	 *	Casting a string to User.
	 */
	private function toUser($sNickname)
	{
		return $this->getUser($sNickname);
	}


	/**
	 *	Casting a string to CoreChannel.
	 */
	private function toChannel($sChannel)
	{
		return $this->getChannel($sChannel);
	}


	/**
	 *	Casting a string to CoreChannel.
	 */
	private function toChan($sChannel)
	{
		return $this->getChannel($sChannel);
	}
}
