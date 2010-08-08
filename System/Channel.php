<?php
/**
 *	Channel class for OUTRAGEbot
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC5 (Git commit: 6ed540be1c064d18188b85640bfea39813091077)
 */


class Channel
{
	/* Define our variables */
	private
		$pMaster = null,
		$sChannel = null;
		
	public
		$aUsers = array(),
		$aTopicInformation = array
		(
			'String' => '',
			'SetBy' => '',
			'Time' => 0
		);
	
	
	/**
	 *	Called when the class is constructed.
	 */
	public function __construct($pMaster, $sChannel)
	{
		$this->pMaster = $pMaster;
		$this->sChannel = strtolower($sChannel);
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
		return isset($this->aUsers[$sNickname]);
	}
	
	
	/**
	 *	Users: Add user to the internal database
	 *	@ignore
	 */
	public function addUserToChannel($sNickname, $sChannelMode = "")
	{
		$this->aUsers[$sNickname] = $sChannelMode;
	}
	
	
	/**
	 *	Users: Rename a user from the internal database
	 *	@ignore
	 */
	public function renameUserInChannel($sOldNickname, $sNewNickname)
	{
		$this->aUsers[$sNewNickname] = $this->aUsers[$sOldNickname];
		unset($this->aUsers[$sOldNickname]);
	}
	
	
	/**
	 *	Users: Add user to the internal database
	 *	@ignore
	 */
	public function modifyUserInChannel($sNickname, $sMode, $sChannelMode = "")
	{		
		if($sMode == '+')
		{
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
		return count($this->aUsers);
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
		
		foreach($this->aUsers as $sNickname => $sChannelMode)
		{		
			$aUsers[] = array
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
	 *	Get the ban list
	 */
	public function getBanList()
	{
		return $this->pMaster->getChannelBanList($this->sChannel);
	}
	
	
	/**
	 *	Get the invite list
	 */
	public function getInviteList()
	{
		return $this->pMaster->getChannelInviteList($this->sChannel);
	}
	
	
	/**
	 *	Get the exception list
	 */
	public function getExceptList()
	{
		return $this->pMaster->getChannelExceptList($this->sChannel);
	}
	
	
	/**
	 *	Get the channel topic information
	 */
	public function getTopic()
	{
		return $this->aTopicInformation;
	}
}