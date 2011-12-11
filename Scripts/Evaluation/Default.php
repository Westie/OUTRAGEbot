<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     5d1624676fbbbfec531270ed6ef862070be017c2
 *	Committed at:   Sun Dec 11 12:42:18 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class Evaluation extends Script
{
	/**
	 *	Store the tokeniser class.
	 */
	private
		$aEnvironment = array(),
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
				extract($this->aEnvironment, EXTR_SKIP);
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

				$this->aEnvironment = get_defined_vars();
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
