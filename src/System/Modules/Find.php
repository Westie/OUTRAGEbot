<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     673e7bc312dc0cd03956efc0d4556fd369986a67
 *	Committed at:   Sun Dec  4 21:28:32 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class ModuleFind
{
	/**
	 *	Called when the module is loaded.
	 */
	static public function initModule()
	{
		Core::introduceFunction("Find", array(__CLASS__, "Find"));
	}


	/**
	 *	Called when someone wants to retrieve matched users. If multiple queries
	 *	have been provided, retrieve each query separately and then remove all
	 *	duplicates.
	 */
	static public function Find($mQueryString, $cCallback = null)
	{
		if(is_array($mQueryString) || $mQueryString instanceof Traversable)
		{
			$aReturnedUsers = array();

			foreach($mQueryString as $sQueryString)
			{
				$aMatchedUsers = self::PerformSearch((string) $sQueryString, $cCallback);

				foreach($aMatchedUsers as $pUser)
				{
					if(!isset($aReturnedUsers[$pUser->hostmask]))
					{
						$aReturnedUsers[$pUser->hostmask] = $pUser;
					}
				}
			}

			return $aReturnedUsers;
		}

		return self::PerformSearch((string) $mQueryString, $cCallback);
	}


	/**
	 *	Individual selector method - retrieves the matched users and returns it.
	 */
	static private function PerformSearch($sQueryString, $cCallback)
	{
		list($aChannelSelectors, $aCriteria) = self::generateSearchCriteria($sQueryString);

		$pInstance = Core::getCurrentInstance();
		$sCompiledPattern = self::compilePattern($aCriteria);

		$aMatchedUsers = array();
		$bCheckChannels = isset($aChannelSelectors[0]);

		foreach($pInstance->pUsers as $pUser)
		{
			if($sCompiledPattern == null || preg_match($sCompiledPattern, $pUser->hostmask))
			{
				if($bCheckChannels)
				{
					if(!self::delegateChannelSelectors($pUser, $aChannelSelectors))
					{
						continue;
					}
				}

				$aMatchedUsers[$pUser->hostmask] = $pUser;

				if($cCallback)
				{
					Core::invokeReflection($cCallback, array(), $pUser);
				}
			}
		}

		return $aMatchedUsers;
	}


	/**
	 *	A method to delegate searching the channel elements.
	 */
	static private function delegateChannelSelectors(CoreUser $pUser, array $aChannelSelectors)
	{
		$bSuccessfulMatch = false;

		foreach($aChannelSelectors as $aChannelElements)
		{
			$bSuccessfulMatch = self::isUserSelected($pUser, $aChannelElements);
		}

		return $bSuccessfulMatch;
	}


	/**
	 *	A method delegated to search the channel elements, and returns whether
	 *	the user is in the channel.
	 *
	 *	I don't like the fact I have to do different things for negations...
	 */
	static private function isUserSelected(CoreUser $pUser, array $aChannelElements)
	{
		$sUser = (string) $pUser;

		foreach($aChannelElements as $pChannel)
		{
			if($pChannel->Negation)
			{
				if($pChannel->Channel->isUserInChannel($pUser))
				{
					if(empty($pChannel->Modes))
					{
						return false;
					}

					$iModeCount = 0;

					foreach($pChannel->Modes as $cMode)
					{
						$sModeString = $pChannel->Channel->aUsers[$sUser];

						if(stristr($sModeString, $cMode) != null)
						{
							++$iModeCount;
						}
					}

					if($iModeCount)
					{
						return false;
					}
				}

				continue;
			}
			else
			{
				if(!$pChannel->Channel->isUserInChannel($pUser))
				{
					return false;
				}

				if(!empty($pChannel->Modes))
				{
					$iModeCount = 0;

					foreach($pChannel->Modes as $cMode)
					{
						$sModeString = $pChannel->Channel->aUsers[$sUser];

						if(stristr($sModeString, $cMode) != null)
						{
							++$iModeCount;
						}
					}

					if(!$iModeCount)
					{
						return false;
					}
				}

				continue;
			}
		}

		return true;
	}


	/**
	 *	This method returns an array of user criteria and channels,
	 *	that is used to match users in the query.
	 */
	static private function generateSearchCriteria($sQueryString)
	{
		$pInstance = Core::getCurrentInstance();

		$aChannels = array();
		$aCriteria = array
		(
			"Nickname" => "%",
			"Username" => "%",
			"Hostname" => "%",
		);

		# Determinate channel selector and query separator.
		if(preg_match('/^[\s]{0,}(.*?)[\s]{0,}\:[\s]{0,}(.*?)[\s]{0,}$/', preg_quote($sQueryString), $aParts))
		{
			$sQueryString = stripslashes($aParts[1]);
			$sCSelectorList = stripslashes($aParts[2]);

			$sPrefixes = $pInstance->getServerConfiguration("PREFIX");
			$sPrefix = substr($sPrefixes, strpos($sPrefixes, ")") + 1);

			$sChanTypes = preg_quote($pInstance->getServerConfiguration("CHANTYPES"));

			$sChannelPattern = "/^([\^]{0,1})([{$sPrefix}]{0,})([{$sChanTypes}])(.*)$/";

			foreach(explode(',', $sCSelectorList) as $sSelector)
			{
				$sSelector = trim($sSelector);

				$aSelector = explode(' ', $sSelector);
				$iSelectorCount = count($aSelector);

				$aChannelElements = array();

				foreach($aSelector as $sChannel)
				{
					if(preg_match($sChannelPattern, $sChannel, $aMatches))
					{
						$aChannelElements[] = (object) array
						(
							"Channel" => $pInstance->getChannel("{$aMatches[3]}{$aMatches[4]}"),
							"Modes" => preg_split('//', CoreUtilities::modeCharToLetter($aMatches[2]), -1, PREG_SPLIT_NO_EMPTY),
							"Negation" => ($aMatches[1] == "^"),
						);
					}
				}

				$aChannels[] = $aChannelElements;
			}
		}

		# Username selector.
		if(preg_match('/^!(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => "%",
				"Username" => trim($aParts[1]),
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
				"Hostname" => trim($aParts[1]),
			);
		}

		# The full works!
		elseif(preg_match('/^(.*?)!(.*?)@(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => trim($aParts[1]),
				"Username" => trim($aParts[2]),
				"Hostname" => trim($aParts[3]),
			);
		}

		# Username and hostname selector.
		elseif(preg_match('/^(.*?)@(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => "%",
				"Username" => trim($aParts[1]),
				"Hostname" => trim($aParts[2]),
			);
		}

		# Nickname selector.
		elseif(preg_match('/^(.*?)$/', $sQueryString, $aParts))
		{
			$aCriteria = array
			(
				"Nickname" => trim($aParts[1]),
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
	static private function compilePattern($aCriteria)
	{
		if($aCriteria["Nickname"] == "%" && $aCriteria["Username"] == "%" && $aCriteria["Hostname"] == "%")
		{
			return null;
		}

		$aCriteria["Nickname"] = preg_quote($aCriteria["Nickname"]);
		$aCriteria["Username"] = preg_quote($aCriteria["Username"]);
		$aCriteria["Hostname"] = preg_quote($aCriteria["Hostname"]);

		$sPattern = "/{$aCriteria['Nickname']}!{$aCriteria['Username']}@{$aCriteria['Hostname']}/s";
		$sPattern = str_replace(array('%'), '(.*?)', $sPattern);

		return $sPattern;
	}
}
