<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     715e888c1cc36aad4bc58e520cffbe92c8304e76
 *	Committed at:   Sat Jun 11 18:17:36 BST 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreChannel extends CoreChild
{
	/* Define our variables */
	private
		$sChannel = null;

	static
		$mTemp = null;

	public
		$pUsers = null,
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

		$this->pUsers = new stdClass();
		$this->pModes = new stdClass();

		$this->pTopic = (object) array
		(
			"topicString" => "",
			"topicTime" => 0,
			"topicSetter" => "",
		);

		foreach($this->pMaster->pConfig->Server->ChannelModes as $sGroupString)
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

		if(strpos($this->internalMasterObject()->pConfig->Server->CHANMODES, $sKey) !== false)
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
	 *	@ignore
	 */
	public function isUserInChannel($sNickname)
	{
		return isset($this->pUsers->$sNickname);
	}


	/**
	 *	Sends stuff to the channel. It's a shortcut, basically.
	 */
	public function __invoke($sMessage, $mOption = SEND_DEF)
	{
		return $this->internalMasterObject()->Message($this->sChannel, $sMessage, $mOption);
	}


	/**
	 *	Users: Add user to the internal database
	 *	@ignore
	 */
	public function addUserToChannel($sNickname, $sChannelMode = "")
	{
		$this->pUsers->$sNickname = $sChannelMode;
	}


	/**
	 *	Users: Rename a user from the internal database
	 *	@ignore
	 */
	public function renameUserInChannel($sOldNickname, $sNewNickname)
	{
		if(!isset($this->pUsers->$sOldNickname) || empty($this->pUsers->$sOldNickname))
		{
			return;
		}

		$this->pUsers->$sNewNickname = $this->pUsers->$sOldNickname;
		unset($this->pUsers->$sOldNickname);

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
			$this->pUsers->$sNickname .= $sChannelMode;
		}
		else
		{
			$this->pUsers->$sNickname = str_replace($sChannelMode, "", $this->pUsers->$sNickname);
		}
	}


	/**
	 *	Users: Remove a user from the internal database
	 *	@ignore
	 */
	public function removeUserFromChannel($sNickname)
	{
		unset($this->pUsers->$sNickname);
	}


	/**
	 *	Get the channel user count.
	 */
	private function propGetCount()
	{
		return count($this->pUsers);
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
		return $this->pMaster->Raw("TOPIC {$this->sChannel} :{$sString}");
	}


	/**
	 *	Get the users in the channel
	 */
	private function propGetUsers()
	{
		$aUsers = array();

		foreach($this->pUsers as $sNickname => $sChannelMode)
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

		$pSocket = $this->pMaster->getCurrentSocket();

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
						'mask' => $pMessage->Parts[4],
						'admin' => $pMessage->Parts[5],
						'time' => $pMessage->Parts[6],
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

		$pSocket = $this->pMaster->getCurrentSocket();

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
						'mask' => $pMessage->Parts[4],
						'admin' => $pMessage->Parts[5],
						'time' => $pMessage->Parts[6],
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

		$pSocket = $this->pMaster->getCurrentSocket();

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
						'mask' => $pMessage->Parts[4],
						'admin' => $pMessage->Parts[5],
						'time' => $pMessage->Parts[6],
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
		if(!isset($this->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qaohv]/', $this->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has half-op in that channel. Half operators
	 *	have the mode ' % ', and may not be available on all networks.
	 */
	public function isUserHalfOp($sUser)
	{
		if(!isset($this->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qaoh]/', $this->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has operator in that channel. Operators have
	 *	the mode ' @ '.
	 */
	public function isUserOp($sUser)
	{
		if(!isset($this->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qao]/', $this->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has admin in that channel. Admins have the
	 *	mode ' & ', and may not be available on all networks.
	 */
	public function isUserAdmin($sUser)
	{
		if(!isset($this->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[qa]/', $this->pUsers->$sUser) == true;
	}


	/**
	 *	Checks if that user has owner in that channel. Owners have the
	 *	mode ' ~ ', and may not be available on all networks.
	 */
	public function isUserOwner($sUser)
	{
		if(!isset($this->pUsers->$sUser))
		{
			return false;
		}

		return preg_match('/[q]/', $this->pUsers->$sUser) == true;
	}


	/**
	 *	Sets the mode on the channel.
	 */
	public function Mode($sModeString)
	{
		return $this->pMaster->Raw("MODE {$this->sChannel} {$sModeString}");
	}


	/**
	 *	Returns the creation time of the channel.
	 */
	public function getCreationTime()
	{
		return $this->iCreateTime;
	}
}
