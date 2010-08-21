<?php
/**
 *	Channel class for OUTRAGEbot
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version 1.1.1-RC5 (Git commit: 270ef40a47f5596acbfc33a3066714c63b05ec47)
 */


class User
{
	/* Define our variables */
	private
		$pMaster = null,
		$sNickname = null;
	
	
	/**
	 *	Called when the class is constructed.
	 */
	public function __construct($pMaster, $sNickname)
	{
		$this->pMaster = $pMaster;
		$this->sNickname = strtolower($sNickname);
	}
	
	
	/**
	 *	Called when the object is converted to string.
	 */
	public function __toString()
	{
		return $this->sNickname;
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
	 *	Get the connection settings
	 */
	private function propGetConnection()
	{
		$aWhois = $this->pMaster->getWhois($this->sNickname);
		
		return array
		(
			'Network' => $aWhois['Connection']['Network'],
			'Address' => $aWhois['Connection']['Address'],
		);
	}
	
	
	/**
	 *	Get the user's details
	 */
	private function propGetDetails()
	{
		$aWhois = $this->pMaster->getWhois($this->sNickname);
		
		return array
		(
			'Nickname' => $this->sNickname,
			'Username' => $aWhois['Details']['Username'],
			'Realname' => $aWhois['Details']['Realname'],
			'Hostname' => $aWhois['Details']['Hostname'],
		);
	}
	
	
	/**
	 *	Get the user's active channels
	 */
	private function propGetChannels()
	{
		$aWhois = $this->pMaster->getWhois($this->sNickname);
		return $aWhois['Channels'];
	}
	
	
	/**
	 *	Get the user's hostname
	 */
	private function propGetHostname()
	{
		$aWhois = $this->pMaster->getWhois($this->sNickname);
		return $aWhois['Details']['Hostname'];
	}
	
	
	/**
	 *	Get the user's nickname
	 */
	public function name()
	{
		return $this->sNickname;
	}
}