<?php
/**
 *	OUTRAGEbot development
 */


class CoreChannel
{
	/* Define our variables */
	private
		$pMaster = null,
		$sChannel = null;
	
	static
		$mTemp = null;
		
	public
		$pUsers = null,
		$pTopic = null;
	
	
	/**
	 *	Called when the class is constructed.
	 */
	public function __construct($pMaster, $sChannel)
	{
		$this->pMaster = $pMaster;
		$this->sChannel = strtolower(trim($sChannel));
		
		$this->pUsers = new stdClass();
		
		$this->pTopic = (object) array
		(
			"chantopic" => "",
			"timestamp" => "",
			"setter" => "",
		);
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
		$sKey = "propGet".$sKey;
		
		if(method_exists($this, $sKey))
		{
			return $this->$sKey();
		}
		
		return null;
	}
	
	
	/**
	 *	Properties: setting psuedo properties.
	 *	@ignore
	 */
	public function __set($sKey, $mValue)
	{
		$sKey = "propSet".$sKey;
		
		if(method_exists($this, $sKey))
		{
			return $this->$sKey($mValue);
		}
		
		return null;
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
	public function renameUserInChannel($sNewNickname, $sOldNickname)
	{
		$this->pUsers->$sNewNickname = $this->pUsers->$sOldNickname;
		unset($this->pUsers->$sOldNickname);
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
		return $this->aTopicInformation['String'];
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
	 *	Called when the object is invoked.
	 */
	public function __invoke($sMessage)
	{
		return $this->pMaster->Message($this->sChannel, $sMessage);
	}
	
	
	/**
	 *	Get the channel topic information
	 */
	public function getTopic()
	{
		return $this->aTopicInformation;
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
}