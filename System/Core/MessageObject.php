<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha

 *	Git commit:     de27c63989d09650b26072cbf7232ec6119048ca
 *	Committed at:   Thu Dec  1 22:42:17 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class MessageObject implements ArrayAccess, Countable, Iterator
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
		$this->Payload = (($iPosition = strpos($sString, ' :', 2)) !== false) ? substr($sString, $iPosition + 2) : '';
	}


	/**
	 *	Return the contents of the object. If there is a payload
	 *	then return that. If not, then return the main string.
	 */
	public function __toString()
	{
		if(empty($this->Payload))
		{
			return $this->Raw;
		}

		return $this->Payload;
	}


	/**
	 *	Countable interface: Counts the parts.
	 */
	public function count()
	{
		return count($this->Parts);
	}


	/**
	 *	ArrayAccess interface: Checks if that part is exists.
	 */
	public function offsetExists($iOffset)
	{
		return isset($this->Parts[$iOffset]);
	}


	/**
	 *	ArrayAccess interface: Returns that part.
	 */
	public function offsetGet($iOffset)
	{
		return $this->Parts[$iOffset];
	}


	/**
	 *	ArrayAccess interface: Sets the offset.
	 */
	public function offsetSet($iOffset, $mValue)
	{
		return false;
	}


	/**
	 *	ArrayAccess interface: Unsets the offset.
	 */
	public function offsetUnset($iOffset)
	{
		return false;
	}


	/**
	 *	Iterator interface: Rewinds the parts array.
	 */
	public final function rewind()
	{
		return reset($this->Parts);
	}


	/**
	 *	Iterator interface: Returns the current part element.
	 */
	public final function current()
	{
		return current($this->Parts);
	}


	/**
	 *	Iterator interface: Returns the part key
	 */
	public final function key()
	{
		return key($this->Parts);
	}


	/**
	 *	Iterator interface: Moves the parts array pointer on by one.
	 */
	public final function next()
	{
		return next($this->Parts);
	}


	/**
	 *	Iterator interface: Checks if the parts key is valid.
	 */
	public final function valid()
	{
		return (key($this->Parts) !== null);
	}
}
