<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     5f0b25489c21ae65471f2289c56a4475a94296dc
 *	Committed at:   Mon Sep 12 18:38:47 BST 2011
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
		if(!class_exists("EvaluationTokeniser"))
		{
			include "Tokeniser.php";
		}

		$this->pTokeniser = new EvaluationTokeniser();
		$this->pTokeniser->bind('$this', $this);
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
			try
			{
				ob_start();

				eval($this->pTokeniser->run($sArguments));
				$aOutput = ob_get_contents();

				ob_end_clean();

				foreach(explode("\n", $aOutput) as $sOutput)
				{
					$sOutput = rtrim($sOutput);

					if(strlen($sOutput) < 1)
					{
						continue;
					}

					$sChannel($sOutput);
				}
			}
			catch(Exception $e)
			{
				$sChannel("Error: ".$e->getMessage());
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
