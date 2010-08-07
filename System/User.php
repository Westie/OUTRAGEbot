<?php
/**
 *	Channel class for OUTRAGEbot
 *
 *	@package OUTRAGEbot
 *	@copyright David Weston (c) 2010 -> http://www.typefish.co.uk/licences/
 *	@author David Weston <westie@typefish.co.uk>
 *	@version <new>
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
	 *	Get the connection settings
	 */
	private function propConnection($mValue = null)
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
	private function propDetails($mValue = null)
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
}