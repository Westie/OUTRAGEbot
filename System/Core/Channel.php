<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     95304f4359b55dae9234c2c1156593d3c5fdb40d
 *	Committed at:   Thu Dec  1 23:01:51 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreChannel extends CoreChild implements ArrayAccess, Countable, Iterator
{
	/*
	 *	Define our variables
	 */
	private
		$sChannel = null;


	static
		$mTemp = null;


	public
		$aUsers = null,
		$pTopic = null,
		$pModes = null,
		$iCreateTime = 0;


	/**
	 *	Called when the class is constructed.
	 */
	public function __construct($pMaster, $sChannel)
	{
		$this->internalMasterObject($pMaster);
		$this->sChannel = strtolower(trim($sChannel));

		$this->aUsers = array();
		$this->pModes = new stdClass();

		$this->pTopic = (object) array
		(
			"topicString" => "",
			"topicTime" => 0,
			"topicSetter" => "",
		);

		$aChannelModes = $this->internalMasterObject()->getServerConfiguration("ChannelModes");

		foreach($aChannelModes as $sGroupString)
		{
			$aCharacters = preg_split('//', $sGroupString);

			foreach($aCharacters as $sCharacter)
			{
				if($sCharacter)
				{
					$this->pModes->$sCharacter = false;
				}
			}
		}
	}


	/**
	 *	Called when the object is converted to string.
	 */
	public function __toString()
	{
		return $this->sChannel;
	}


	/**
	 *	Properties: accessing psuedo properties.
	 *	@ignore
	 */
	public function __get($sKey)
	{
		$sProperty = "propGet".$sKey;

		if(method_exists($this, $sProperty))
		{
			return $this->$sProperty();
		}

		if(strpos($this->internalMasterObject()->getServerConfiguration("CHANMODES"), $sKey) !== false)
		{
			return $this->pModes->$sKey;
		}

		return null;
	}


	/**
	 *	Properties: setting psuedo properties.
	 *	@ignore
	 */
	public function __set($sKey, $mValue)
	{
		$sProperty = "propSet".$sKey;

		if(method_exists($this, $sProperty))
		{
			return $this->$sProperty($mValue);
		}
	}


	/**
	 *	Users: Checks if a user is in the database
	 */
	public function isUserInChannel($sNickname)
	{
		if($sNickname instanceof CoreUser)
		{
			$sNickname = $sNickname->sNickname;
		}

		return isset($this->aUsers[$sNickname]);
	}


	/**
	 *	Sends stuff to the channel. It's a shortcut, basically.
	 */
	public function __invoke($sMessage, $mOption = SEND_DEF)
	{
		return $this->internalMasterObject()->Message($this->sChannel, $sMessage, $mOption);
	}


	/**
	 *	Send stuff to the channel.
	 */
	public function Message($sMessage, $mOption = SEND_DEF)
	{
		return $this->internalMasterObject()->Message($this->sChannel, $sMessage, $mOption);
	}


	/**
	 *	Send actions to the channel.
	 */
	public function Action($sMessage, $mOption = SEND_DEF)
	{
		return $this->internalMasterObject()->Action($this->sChannel, $sMessage, $mOption);
	}


	/**
	 *	Retrieves a list of all users matching a pattern.
	 *	It uses the same pattern prototypes as its parent function, ModuleFind::Find();
	 */
	public function Find($sPattern)
	{
		$aPatternSegments = explode($sPattern, ':', 2);

		if(!isset($aPatternSegments[1]))
		{
			$aPatternSegments[1] = "{$this->sChannel}";
		}
		else
		{
			$aPatternSegments[1] = trim($aPatternSegments[1]).",{$this->sChannel}";
		}

		return $this->internalMasterObject()->Find($sPattern);
	}


	/**
	 *	Users: Add user to the internal database
	 *	@ignore
	 */
	public function addUserToChannel($sNickname, $sChannelMode = "")
	{
		if($sChannelMode === null)
		{
			if(!isset($this->aUsers[$sNickname]))
			{
				$this->aUsers[$sNickname] = "";
			}
		}
		else
		{
			$this->aUsers[$sNickname] = $sChannelMode;
		}
	}


	/**
	 *	Users: Rename a user from the internal database
	 *	@ignore
	 */
	public function renameUserInChannel($sOldNickname, $sNewNickname)
	{
		if(!isset($this->aUsers[$sOldNickname]))
		{
			return;
		}

		$this->aUsers[$sNewNickname] = $this->aUsers[$sOldNickname];
		unset($this->aUsers[$sOldNickname]);

		return;
	}


	/**
	 *	Users: Add user to the internal database
	 *	@ignore
	 */
	public function modifyUserInChannel($sNickname, $sMode, $sChannelMode = "")
	{
		if($sMode == '+')
		{
			if(!isset($this->aUsers[$sNickname]))
			{
				$this->aUsers[$sNickname] = "";
			}

			$this->aUsers[$sNickname] .= $sChannelMode;
		}
		else
		{
			$this->aUsers[$sNickname] = str_replace($sChannelMode, "", $this->aUsers[$sNickname]);
		}
	}


	/**
	 *	Users: Remove a user from the internal database
	 *	@ignore
	 */
	public function removeUserFromChannel($sNickname)
	{
		unset($this->aUsers[$sNickname]);
	}


	/**
	 *	Get the channel user count.
	 */
	private function propGetCount()
	{
		$iCount = 0;

		foreach($this->aUsers as $pUser)
		{
			++$iCount;
		}

		return $iCount;
	}


	/**
	 *	Get the channel topic.
	 */
	private function propGetTopic()
	{
		return $this->pTopic->topicString;
	}


	/**
	 *	Set the channel topic
	 */
	private function propSetTopic($sString)
	{
		return $this->internalMasterObject()->Raw("TOPIC {$this->sChannel} :{$sString}");
	}


	/**
	 *	Get the users in the channel
	 */
	private function propGetUsers()
	{
		$aUsers = array();

		foreach($this->aUsers as $sNickname => $sChannelMode)
		{
			$aUsers[] = (object) array
			(
				"Nickname" => $sNickname,
				"Usermode" => $sChannelMode,
			);
		}

		return $aUsers;
	}


	/**
	 *	Get the channel topic information
	 */
	public function getTopic()
	{
		return $this->pTopic;
	}


	/**
	 *	Get the channel ban list.
	 */
	public function getBanList()
	{
		CoreChannel::$mTemp = array();

		$pSocket = $this->internalMasterObject()->getCurrentSocket();

		$pSocket->Output("MODE {$this->sChannel} +b");

		$pSocket->executeCapture(function($sString)
		{
			$pMessage = Core::getMessageObject($sString);

			switch($pMessage->Numeric)
			{
				case "367":
				{
					CoreChannel::$mTemp[] = (object) array
					(
						'hostmaskString' => $pMessage->Parts[4],
						'hostmaskObject' => CoreMaster::parseHostmask($pMessage->Parts[4]),
						'modeSetter' => $pMessage->Parts[5],
						'modeTime' => $pMessage->Parts[6],
					);

					return false;
				}

				case "368":
				{
					return true;
				}
			}

			return false;
		});

		$mTemp = CoreChannel::$mTemp;
		CoreChannel::$mTemp = null;

		return $mTemp;
	}


	/**
	 *	Get the channel invite list.
	 */
	public function getInviteList()
	{
		CoreChannel::$mTemp = array();

		$pSocket = $this->internalMasterObject()->getCurrentSocket();

		$pSocket->Output("MODE {$this->sChannel} +I");

		$pSocket->executeCapture(function($sString)
		{
			$pMessage = Core::getMessageObject($sString);

			switch($pMessage->Numeric)
			{
				case "346":
				{
					CoreChannel::$mTemp[] = (object) array
					(
						'hostmaskString' => $pMessage->Parts[4],
						'hostmaskObject' => CoreMaster::parseHostmask($pMessage->Parts[4]),
						'modeSetter' => $pMessage->Parts[5],
						'modeTime' => $pMessage->Parts[6],
					);

					return false;
				}

				case "347":
				{
					return true;
				}
			}

			return false;
		});

		$mTemp = CoreChannel::$mTemp;
		CoreChannel::$mTemp = null;

		return $mTemp;
	}


	/**
	 *	Get the channel exception list.
	 */
	public function getExceptionList()
	{
		CoreChannel::$mTemp = array();

		$pSocket = $this->internalMasterObject()->getCurrentSocket();

		$pSocket->Output("MODE {$this->sChannel} +e");

		$pSocket->executeCapture(function($sString)
		{
			$pMessage = Core::getMessageObject($sString);

			switch($pMessage->Numeric)
			{
				case "348":
				{
					CoreChannel::$mTemp[] = (object) array
					(
						'hostmaskString' => $pMessage->Parts[4],
						'hostmaskObject' => CoreMaster::parseHostmask($pMessage->Parts[4]),
						'modeSetter' => $pMessage->Parts[5],
						'modeTime' => $pMessage->Parts[6],
					);

					return false;
				}

				case "349":
				{
					return true;
				}
			}

			return false;
		});

		$mTemp = CoreChannel::$mTemp;
		CoreChannel::$mTemp = null;

		return $mTemp;
	}


	/**
	 *	Checks if that user has voice in that channel. Voicers have the
	 *	mode ' + '.
	 */
	public function isUserVoice($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		if(!isset($this->aUsers[$sUser]))
		{
			return false;
		}

		return preg_match('/[qaohv]/', $this->aUsers[$sUser]) == true;
	}


	/**
	 *	Checks if that user has half-op in that channel. Half operators
	 *	have the mode ' % ', and may not be available on all networks.
	 */
	public function isUserHalfOp($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		if(!isset($this->aUsers[$sUser]))
		{
			return false;
		}

		return preg_match('/[qaoh]/', $this->aUsers[$sUser]) == true;
	}


	/**
	 *	Checks if that user has operator in that channel. Operators have
	 *	the mode ' @ '.
	 */
	public function isUserOp($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		if(!isset($this->aUsers[$sUser]))
		{
			return false;
		}

		return preg_match('/[qao]/', $this->aUsers[$sUser]) == true;
	}


	/**
	 *	Checks if that user has admin in that channel. Admins have the
	 *	mode ' & ', and may not be available on all networks.
	 */
	public function isUserAdmin($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		if(!isset($this->aUsers[$sUser]))
		{
			return false;
		}

		return preg_match('/[qa]/', $this->aUsers[$sUser]) == true;
	}


	/**
	 *	Checks if that user has owner in that channel. Owners have the
	 *	mode ' ~ ', and may not be available on all networks.
	 */
	public function isUserOwner($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		if(!isset($this->aUsers[$sUser]))
		{
			return false;
		}

		return preg_match('/[q]/', $this->aUsers[$sUser]) == true;
	}


	/**
	 *	Sets the mode on the channel.
	 */
	public function Mode($sModeString)
	{
		return $this->internalMasterObject()->Raw("MODE {$this->sChannel} {$sModeString}");
	}


	/**
	 *	Returns the creation time of the channel.
	 */
	public function getCreationTime()
	{
		return $this->iCreateTime;
	}


	/**
	 *	Countable interface: Returns the number of users in the channel.
	 */
	public function count()
	{
		return $this->propGetCount();
	}


	/**
	 *	ArrayAccess interface: Checks if the user is in the channel.
	 */
	public function offsetExists($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		return isset($this->aUsers[$sUser]);
	}


	/**
	 *	ArrayAccess interface: Returns the offset.
	 *
	 *	Yeah, it's stupid, I know. Let's just return the key at the moment,
	 *	'cos there's no usable user object.
	 */
	public function offsetGet($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		return $sUser;
	}


	/**
	 *	ArrayAccess interface: Sets the offset.
	 */
	public function offsetSet($sUser, $mValue)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		return false;
	}


	/**
	 *	ArrayAccess interface: Unsets the offset.
	 */
	public function offsetUnset($sUser)
	{
		if($sUser instanceof CoreUser)
		{
			$sUser = $sUser->sNickname;
		}

		return false;
	}


	/**
	 *	Iterator interface: Rewinds the user array.
	 */
	public final function rewind()
	{
		return reset($this->aUsers);
	}


	/**
	 *	Iterator interface: Returns the current user element.
	 */
	public final function current()
	{
		return $this->internalMasterObject()->getUser(key($this->aUsers));
	}


	/**
	 *	Iterator interface: Returns the user key
	 */
	public final function key()
	{
		return key($this->aUsers);
	}


	/**
	 *	Iterator interface: Moves the user array pointer on by one.
	 */
	public final function next()
	{
		return next($this->aUsers);
	}


	/**
	 *	Iterator interface: Checks if the user key is valid.
	 */
	public final function valid()
	{
		return (key($this->aUsers) !== null);
	}
}
