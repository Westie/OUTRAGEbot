<?php
/**
 *	OUTRAGEbot - PHP 5.3 based IRC bot
 *
 *	Author:		David Weston <westie@typefish.co.uk>
 *
 *	Version:        2.0.0-Alpha
 *	Git commit:     09c68fbaed58f5eaf8f1066c15fd6277f02d8812
 *	Committed at:   Sat Nov 26 19:53:04 GMT 2011
 *
 *	Licence:	http://www.typefish.co.uk/licences/
 */


class CoreUser extends CoreChild implements ArrayAccess, Countable, Iterator
{
	/**
	 *	Store our variables.
	 */
	public
		$sNickname,
		$pHostmaskObject,
		$pWhois;


	private
		$iWhoisExpire = null;


	/**
	 *	Called when the class is constructed.
	 */
	public function __construct($pMaster, $sNickname)
	{
		$this->sNickname = $sNickname;

		$this->internalMasterObject($pMaster);
	}


	/**
	 *	Called when the object is converted to string.
	 */
	public function __toString()
	{
		return $this->sNickname;
	}


	/**
	 *	Sends stuff to the user. It's a shortcut, basically.
	 */
	public function __invoke($sMessage, $mOption = SEND_DEF)
	{
		return $this->internalMasterObject()->Message($this->sNickname, $sMessage, $mOption);
	}


	/**
	 *	Returns the user's client version.
	 */
	public function __call($sFunction, $aArguments)
	{
		return call_user_func(Core::$pFunctionList->requestCTCPMessage, $this->sNickname, $sFunction.' '.implode(' ', $aArguments));
	}


	/**
	 *	Update the internal WHOIS cache.
	 */
	public function getWhois()
	{
		$this->pWhois = call_user_func(Core::$pFunctionList->getWhoisData, $this->sNickname);
		$this->pWhois->hostmask = $this->hostmask;

		$this->iWhoisExpire = time() + 60;
	}


	/**
	 *	Sends stuff to the channel. It's a shortcut, basically.
	 */
	public function Message($sMessage, $mOption = SEND_DEF)
	{
		return $this->internalMasterObject()->Message($this->sNickname, $sMessage, $mOption);
	}


	/**
	 *	Retrieve a member of the WHOIS object.
	 */
	public function __get($sKey)
	{
		switch($sKey)
		{
			case 'hostmask':
			{
				$pHostmask = $this->pHostmaskObject;
				return "{$this->sNickname}!{$pHostmask->Username}@{$pHostmask->Hostname}";
			}

			default:
			{
				if($this->iWhoisExpire == null || $this->iWhoisExpire < time())
				{
					$this->getWhois();
				}

				if(isset($this->pWhois->$sKey))
				{
					return $this->pWhois->$sKey;
				}

				return null;
			}
		}
	}


	/**
	 *	Countable interface:
	 */
	public function count()
	{
		return;
	}


	/**
	 *	ArrayAccess interface:
	 */
	public function offsetExists($sChannel)
	{
		return;
	}


	/**
	 *	ArrayAccess interface:
	 *
	 *	Yeah, it's stupid, I know. Let's just return the key at the moment,
	 *	'cos there's no usable user object.
	 */
	public function offsetGet($sChannel)
	{
		return;
	}


	/**
	 *	ArrayAccess interface:
	 */
	public function offsetSet($sChannel, $mValue)
	{
		return;
	}


	/**
	 *	ArrayAccess interface:
	 */
	public function offsetUnset($sChannel)
	{
		return;
	}


	/**
	 *	Iterator interface:
	 */
	public final function rewind()
	{
		return;
	}


	/**
	 *	Iterator interface:
	 */
	public final function current()
	{
		return;
	}


	/**
	 *	Iterator interface:
	 */
	public final function key()
	{
		return;
	}


	/**
	 *	Iterator interface:
	 */
	public final function next()
	{
		return;
	}


	/**
	 *	Iterator interface: Checks if the user key is valid.
	 */
	public final function valid()
	{
		return;
	}
}
