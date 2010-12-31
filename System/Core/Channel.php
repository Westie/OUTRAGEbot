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
		
	public
		$pUsers = null,
		$pTopic = null;
	
	
	/**
	 *	Called when the class is constructed.
	 */
	public function __construct($pMaster, $sChannel)
	{
		$this->pMaster = $pMaster;
		$this->sChannel = strtolower($sChannel);
		
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
	public function renameUserInChannel($sOldNickname, $sNewNickname)
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
	public function propGetUsers()
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
}