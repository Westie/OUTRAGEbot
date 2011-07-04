<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     c4b0310d54d08608fa7e83818ebf75150aa23aee
 *	Committed at:   Mon Jul  4 20:50:17 BST 2011
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
		$this->Numeric = strtoupper($this->Parts[1]);
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
