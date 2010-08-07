<?php
/**
 *	Channel class for OUTRAGEbot
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version <new>
 */


class Channel
{
	/* Define our variables */
	private
		$pMaster = null,
		$sChannel = null;
	
	
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
		$sKey = "prop".$sKey;
		
		if(method_exists($this, $sKey))
		{
			return $this->$sKey();
		}
		
		return null;
	}
	
	
	/**
	 *	Properties: setting psuedo properties
	 *	@ignore
	 */
	public function __set($sKey, $mValue)
	{
		$sKey = "prop".$sKey;
		
		if(method_exists($this, $sKey))
		{
			return $this->$sKey($mValue);
		}
		
		return null;
	}
	
	
	/**
	 *	Get the channel user count.
	 *	@ignore
	 */
	private function propCount($mValue = null)
	{
		return $this->pMaster->getChannelUserCount($this->sChannel);
	}
	
	
	/**
	 *	Get the channel topic.
	 *	@ignore
	 */
	private function propTopic($mValue = null)
	{
		return $this->pMaster->pModes->aChannelInfo[$this->sChannel]['TopicString'];
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
		return $this->pMaster->getChannelTopic($this->sChannel);
	}
	
	
	/**
	 *	Get the users in the channel
	 */
	public function getUsers()
	{
		$aUsers = array();
		
		foreach($this->pMaster->pModes->aChannels[$this->sChannel] as $sKey => $aUser)
		{
			$iUserMode = $aUser['iMode'];
			$sUserMode = StaticLibrary::userModeToChar($iUserMode);
			
			$aUsers[] = array
			(
				"Nickname" => $sKey,
				"Usermode" => $sUserMode,
			);
		}
		
		return $aUsers;
	}
}