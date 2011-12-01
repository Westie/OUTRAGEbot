<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Beta

 *	Git commit:     b882ae6528fa3950a03f50ec895ea670f8541f26
 *	Committed at:   Thu Dec  1 22:35:20 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class ModuleFind
{
	/**
	 *	Called when the module is loaded.
	 */
	static function initModule()
	{
		Core::introduceFunction("Find", array(__CLASS__, "Find"));
	}


	/**
	 *	Called when someone wants to retrieve matched users.
	 */
	static function Find($sQueryString, $cCallback = null)
	{
		list($aChannels, $aCriteria) = self::generateSearchCriteria($sQueryString);

		$pInstance = Core::getCurrentInstance();
		$sCompiledPattern = self::compilePattern($aCriteria);

		$aMatchedUsers = array();
		$bCheckChannels = isset($aChannels[0]);

		foreach($pInstance->pUsers as $pUser)
		{
			if(preg_match($sCompiledPattern, $pUser->hostmask))
			{
				if($bCheckChannels)
				{
					$iChannels = 0;

					foreach($aChannels as $pChannel)
					{
						if($pChannel->channel->isUserInChannel($pUser))
						{
							if($pChannel->modes !== null)
							{
								foreach($pChannel->modes as $cMode)
								{
									if(stristr($pChannel->channel->aUsers[$pUser->sNickname], $cMode))
									{
										++$iChannels;
									}
								}
							}
							else
							{
								++$iChannels;
							}
						}
					}

					if(!$iChannels)
					{
						continue;
					}
				}

				$aMatchedUsers[] = $pUser;

				if($cCallback)
				{
					Core::invokeReflection($cCallback, array(), $pUser);
				}
			}
		}

		return $aMatchedUsers;
	}


	/**
	 *	This method returns an array of user criteria and channels,
	 *	that is used to match users in the query.
	 */
	static function generateSearchCriteria($sQueryString)
	{
		$pInstance = Core::getCurrentInstance();

		$aChannels = array();
		$aCriteria = array
		(
			"Nickname" => "%",
			"Username" => "%",
			"Hostname" => "%",
		);

		$sQueryString = preg_quote($sQueryString);


		# Channel query and/or selector.
		if(preg_match('/^[\s]{0,}(.*?)[\s]{0,}\:[\s]{0,}(.*?)[\s]{0,}$/', $sQueryString, $aParts))
		{
			$sQueryString = $aParts[1];

			foreach(explode(',', $aParts[2]) as $sChannel)
			{
				$aChannel = explode(':', $sChannel);

				$aChannel[0] = trim($aChannel[0]);

				if(isset($aChannel[1]))
				{
					$aChannel[1] = trim($aChannel[1]);
					$aChannel[1] = preg_split('//', CoreUtilities::modeCharToLetter($aChannel[1]), -1, PREG_SPLIT_NO_EMPTY);
				}
				else
				{
					$aChannel[1] = null;
				}

				$aChannels[] = (object) array
				(
					"channel" => $pInstance->getChannel($aChannel[0]),
					"modes" => $aChannel[1],
				);
			}
		}

		# Username selector.
		if(preg_match('/^!(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => "%",
				"Username" => $aParts[1],
				"Hostname" => "%",
			);
		}

		# Hostname selector.
		elseif(preg_match('/^@(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => "%",
				"Username" => "%",
				"Hostname" => $aParts[1],
			);
		}

		# The full works!
		elseif(preg_match('/^(.*?)!(.*?)@(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => $aParts[1],
				"Username" => $aParts[2],
				"Hostname" => $aParts[3],
			);
		}

		# Username and hostname selector.
		elseif(preg_match('/^(.*?)@(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => "%",
				"Username" => $aParts[1],
				"Hostname" => $aParts[2],
			);
		}

		# Nickname selector.
		elseif(preg_match('/^(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => $aParts[1],
				"Username" => "%",
				"Hostname" => "%",
			);
		}

		return array
		(
			$aChannels,
			$aCriteria,
		);
	}


	/**
	 *	Compile a pattern to search hostmasks for.
	 */
	static function compilePattern($aCriteria)
	{
		$sPattern = "/{$aCriteria['Nickname']}!{$aCriteria['Username']}@{$aCriteria['Hostname']}/s";
		$sPattern = str_replace(array('%'), '(.*?)', $sPattern);

		return $sPattern;
	}
}
