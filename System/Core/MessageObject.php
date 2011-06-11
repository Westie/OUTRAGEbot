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


class MessageObject
{
	public
		$Raw = null,
		$Parts = null,
		$Numeric = null,
		$User = null,
		$Payload = null;


	/**
	 *	Called when the message object is loaded.
	 */
	public function __construct($sString)
	{
		$this->Raw = $sString;
		$this->Parts = explode(' ', $sString);
		$this->Numeric = $this->Parts[1];
		$this->User = CoreMaster::parseHostmask(substr($this->Parts[0], 1));
		$this->Payload = (($iPosition = strpos($sString, ':', 2)) !== false) ? substr($sString, $iPosition + 1) : '';
	}


	/**
	 *	Return the contents of the object. If there is a payload
	 *	then return that. If not, then return the main string.
	 */
	public function __toString()
	{
		if($this->Payload == '')
		{
			return $this->Raw;
		}

		return $this->Payload;
	}
}
